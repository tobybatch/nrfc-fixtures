<?php

namespace App\Service;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\DTO\ImportExportDTO;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ImportExportService
{
    private EntityManagerInterface $entityManager;
    private ClubRepository $clubRepo;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ClubRepository $clubRepo, LoggerInterface $logger)
    {
        $this->entityManager = $em;
        $this->clubRepo = $clubRepo;
        $this->logger = $logger;
    }

    public function readClubsFromCsvFile(mixed $handle): ImportExportDTO {
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

                $club = $this->clubRepo->findOneByNameInsensitive($name) ?? new Club();
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
                
                $this->logger->info($club);

                $this->entityManager->persist($club);
                $status->incrementSuccessCount();
            } catch (\Throwable $e) {
                $status->addError("Club row $rowNum: " . $e->getMessage());
            }
        }
        $this->entityManager->flush();

        return $status;
    }

    public function readFixturesFromCsvFile(mixed $handle): ImportExportDTO
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

                $name = new \DateTimeImmutable($row['Name'] ?? '');
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
}