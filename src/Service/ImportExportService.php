<?php

namespace App\Service;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\DTO\ImportExportDTO;
use App\DTO\TeamImportDTO;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use App\Service\TeamService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class ImportExportService
{
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepo;
    private TeamService $teamService;
    private DateTimeService $datetimeService;
    private LoggerInterface $logger;
    private CompetitionService $competitionService;
    private FixtureRepository $fixtureRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClubRepository         $clubRepo,
        TeamService            $teamService,
        DateTimeService        $datetimeService,
        CompetitionService     $competitionService,
        LoggerInterface        $logger, FixtureRepository $fixtureRepository,
    )
    {
        $this->entityManager = $em;
        $this->clubRepo = $clubRepo;
        $this->teamService = $teamService;
        $this->datetimeService = $datetimeService;
        $this->competitionService = $competitionService;
        $this->logger = $logger;
        $this->fixtureRepository = $fixtureRepository;
    }

    /**
     * @param mixed $handle
     * @param array<Club> $clubs
     * @return void
     */
    public function writeClubsToCsvResource(mixed $handle, array $clubs) : void
    {
        fputcsv($handle, ['Name', 'Address', 'Latitude', 'Longitude', 'Notes', 'Aliases']);
        foreach ($clubs as $club) {
            fputcsv($handle, [
                $club->getName(),
                $club->getAddress(),
                $club->getLatitude(),
                $club->getLongitude(),
                $club->getNotes(),
                $club->getAliases() ? json_encode($club->getAliases()) : '',
            ]);
        }
    }

    public function readClubsFromCsvResource(mixed $handle): ImportExportDTO {
        $status = new ImportExportDTO();

        $header = fgetcsv($handle);
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            try {
                $row = array_combine($header, $data);
                $name = trim($row['Name'] ?? '');
                if ($name === '') {
                    $status->addError("Club row $rowNum: Missing name.");
                    continue;
                }

                $isUpdate = true;
                $club = $this->clubRepo->findOneByNameInsensitive($name);
                if ($club === null) {
                    $club = new Club();
                    $isUpdate = false;
                }
                $club->setName($name);
                $club->setAddress($row['Address'] ?? null);
                $club->setLatitude(isset($row['Latitude']) ? (float)$row['Latitude'] : null);
                $club->setLongitude(isset($row['Longitude']) ? (float)$row['Longitude'] : null);
                $club->setNotes($row['Notes'] ?? null);

                if (!empty($row['Aliases'])) {
                    $aliases = json_decode($row['Aliases'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($aliases)) {
                        $club->setAliases($aliases);
                    } else {
                        $status->addError("Club row $rowNum: Invalid JSON in 'Aliases'.");
                    }
                }

                $this->entityManager->persist($club);
                if ($isUpdate) {
                    $status->incrementUpdateCount();
                } else {
                    $status->incrementSuccessCount();
                }
            } catch (\Throwable $e) {
                $status->addError("Club row $rowNum: " . $e->getMessage());
            }
        }
        $this->entityManager->flush();

        return $status;
    }

    public function readFixturesFromCsvResource(mixed $handle): ImportExportDTO
    {
        $status = new ImportExportDTO();

        $header = fgetcsv($handle);
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            try {
                $row = array_combine($header, $data);

                $club = $this->clubRepo->findOneByNameInsensitive(trim($row['Club'] ?? ''));
                if (!$club) {
                    $status->addError("Fixture row $rowNum: Club '{$row['Club']}' not found.");
                    continue;
                }

                $name = $row['Name'] ?? '';
                $date = new \DateTimeImmutable($row['Date'] ?? 'now');
                $homeAway = HomeAway::tryFrom($row['HomeAway'] ?? '');
                $competition = Competition::tryFrom($row['Competition'] ?? '');
                $team = Team::tryFrom($row['Team'] ?? '');
                $opponent = isset($row['Opponent']) ? Team::tryFrom($row['Opponent']) : null;

                if (!$homeAway || !$competition || !$team) {
                    $status->addError("Fixture row $rowNum: Invalid enum value.");
                    continue;
                }

                $fixture = new Fixture();
                $fixture->setName($name);
                $fixture->setDate($date);
                $fixture->setClub($club);
                $fixture->setHomeAway($homeAway);
                $fixture->setCompetition($competition);
                $fixture->setTeam($team);
                $fixture->setOpponent($opponent);
                $fixture->setName($row['Name'] ?? null);
                $fixture->setNotes($row['Notes'] ?? null);

                $this->entityManager->persist($fixture);
                $status->incrementSuccessCount();
            } catch (\Throwable $e) {
                $status->addError("Fixture row $rowNum: " . $e->getMessage());
            }
        }
        return $status;
    }

    public function importFixtures(mixed $handle): ImportExportDTO
    {
        $status = new ImportExportDTO();
        $headers = fgetcsv($handle);
        $teams = [];

        // Headers should be Date, Team name, Team name, ....
        for ($i = 1; $i < count($headers); $i++) {
            $team = $this->teamService->getBy(trim($headers[$i]));
            if ($team) {
                $teams[] = new TeamImportDTO($team, $i);
            } else {
                $status->addError(sprintf("Fixture header $i: Team '%s' could not be found.", $i));
            }
        }

        // Training
        // Team (H)
        // Team (H) [Comp]
        while (($row = fgetcsv($handle)) !== false) {
            // date
            if (empty($row[0])) {
                $status->addError(sprintf("Fixture row %d: Date is empty.", $i));
                continue;
            }
            try {
                $date = $this->datetimeService->parseUkDateWithOptionalTime($row[0]);
            } catch (InvalidArgumentException|DateMalformedStringException $e) {
                $this->logger->error($e->getMessage());
                $status->addError(sprintf("Fixture row %d: Date must be in the format dd/mm/yyyy, %s provided.", $i, $row[0]));
                continue;
            }

            foreach ($teams as $teamDto) {
                $team = $teamDto->getTeam();
                $col = $teamDto->getColumn();
                $name = $row[$col];
                if (empty($name)) {
                    continue;
                }
                $fixture = $this->fixtureRepository->findOneBy(['date' => $date, 'name' => $name]);
                if (!$fixture) {
                    $fixture = new Fixture();
                }
                $fixture->setName($name);
                $fixture->setDate($date);
                $fixture->setTeam($team);
                // early check for training
                if (strtolower($name) === 'training') {
                    $fixture->setCompetition(Competition::Training);
                    $this->entityManager->persist($fixture);
                    continue;
                }
                // early check for minis festival
                if (strtolower($name) === 'norwich mini festival') {
                    if ($team->value === Team::Minis) {
                        $fixture->setCompetition(Competition::Festival);
                        $this->entityManager->persist($fixture);
                        continue;
                    } else {
                        continue;
                    }
                }

                // try and do club and opponent
                $clubAndTeam = trim(substr($name, 0, strpos($name, '(')));
                if (!empty($clubAndTeam)) {
                    $club = $this->clubRepo->findByNameStartingWith($clubAndTeam);
                    if ($club) {
                        $fixture->setClub($club);
                        $oppo = $this->teamService->findOpponent($clubAndTeam, $team);
                        $fixture->setOpponent($oppo);
                    }
                }
                // try and do home/away
                if (preg_match('/\(([A-Za-z])\)/', $name, $matches)) {
                    $letter = $matches[1];
                    if ($letter === 'A') {
                        $fixture->setHomeAway(HomeAway::Away);
                    } elseif ($letter === 'H') {
                        $fixture->setHomeAway(HomeAway::Home);
                    } else {
                        $fixture->setHomeAway(HomeAway::TBA);
                    }
                } else {
                    $fixture->setHomeAway(HomeAway::TBA);
                }
                // try and find competition
                $fixture->setCompetition($this->competitionService->parseCompetition($team, $name));
                $this->entityManager->persist($fixture);
            }
        }

        $this->entityManager->flush();
        return $status;
    }
}