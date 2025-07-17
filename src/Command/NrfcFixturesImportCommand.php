<?php

// src/Command/ImportCsvCommand.php

namespace App\Command;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture as FixtureEntity;
use App\Repository\ClubRepository;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

#[AsCommand(
    name: 'nrfc:fixtures:import',
    description: 'Import data from CSV file and create entities'
)]
class NrfcFixturesImportCommand extends Command
{
    private ObjectManager $em;
    private ClubRepository $clubRepository;
    private TeamService $teamService;

    public function __construct(EntityManagerInterface $em, TeamService $teamService, ClubRepository $clubRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->teamService = $teamService;
        $this->clubRepository = $clubRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import data from CSV file and create entities')
            ->setHelp('This command allows you to import fixture data from a CSV file and create corresponding entities in the database.')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'club/fixture', 'fixture')
            // ->addOption('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'CSV delimiter', ',')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Flush batch size', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $type = $input->getOption('type');
        // $delimiter = $input->getOption('delimiter');
        $delimiter = ',';
        $batchSize = (int) $input->getOption('batch-size');

        // Skip validation for help command
        if ($input->hasOption('help') && $input->getOption('help')) {
            return Command::SUCCESS;
        }

        if ('fixture' != $type && 'club' != $type) {
            $io->error('Invalid type');

            return Command::FAILURE;
        }

        // Validate file exists
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(sprintf('File "%s" not found', $filePath));
        }

        $io->title(sprintf('Starting import from %s', $filePath));

        $file = fopen($filePath, 'r');
        $importedCount = 0;
        $rowNumber = 0;

        // Process title row
        $row = fgetcsv($file, 0, $delimiter);
        $teamList = [];
        if ('fixture' == $type) {
            foreach ($row as $column => $team) {
                $t = $this->teamService->getBy(trim($team));
                if (!$t) {
                    $io->warning(sprintf('Team "%s" not found', $team));
                    continue;
                }
                $teamList[$t->value] = $column;
            }
        }

        try {
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                ++$rowNumber;

                try {
                    if ('fixture' == $type) {
                        if (empty($row[0])) {
                            $io->warning(sprintf('Row %d, has no date: %s', $rowNumber, implode(', ', $row)));
                        }
                        $_date = \DateTime::createFromFormat('j-M-y', $row[0]);
                        if (!$_date) {
                            $io->warning(sprintf('Row %d has an invalid date: %s', $rowNumber, implode(', ', $row)));
                            continue;
                        }
                        $date = \DateTimeImmutable::createFromMutable($_date)->setTime(0, 0);
                        foreach ($teamList as $t => $column) {
                            $team = $this->teamService->getBy($t);
                            if (!$team) {
                                $io->warning(sprintf('Team "%s" not found', $t));
                                continue;
                            }
                            $this->createFixture($team, $date, $row[$column]);
                        }
                    } else {
                        $this->processClubRow($row);
                    }

                    ++$importedCount;

                    // Batch processing
                    if (0 === $importedCount % $batchSize) {
                        $this->em->flush();
                        $this->em->clear(); // Detaches all objects from Doctrine
                        $io->comment(sprintf('Processed %d records', $importedCount));
                    }
                } catch (\Exception $e) {
                    $io->warning(sprintf(
                        'Error processing row %d: %s. Row data: %s',
                        $rowNumber,
                        $e->getMessage(),
                        implode(',', $row)
                    ));
                    continue;
                }
            }

            // Flush any remaining objects
            $this->em->flush();
            $this->em->clear();

            fclose($file);

            $io->success(sprintf('Successfully imported %d records', $importedCount));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Import failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * @param string[] $row
     */
    private function processClubRow(array $row): void
    {
        $c = $this->clubRepository->findOneBy(['name' => $row[0]]);
        if (null == $c) {
            $c = new Club();
            $c->setName($row[0]);
        }
        $c->setAddress($row[1]);
        $c->setLatitude((float) $row[2]);
        $c->setLongitude((float) $row[3]);
        $this->em->persist($c);
    }

    private function createFixture(Team $team, \DateTimeImmutable $date, mixed $detail): void
    {
        $fixture = new FixtureEntity();
        list($sessionName, $comp, $home, $club, $opponent) = $this->parseDetail($detail);
        $fixture->setTeam($team);
        $fixture->setClub($club);
        $fixture->setName($sessionName);
        $fixture->setCompetition($comp);
        $fixture->setHomeAway($home);
        $fixture->setDate($date);
        $fixture->setTeam($team);
        $fixture->setOpponent($opponent);
        //        $this->io->info(sprintf('Creating fixture for %s on %s', $team->value, $date->format('Y-m-d')));

        $this->em->persist($fixture);
    }

    /**
     * @return array{string, Competition, HomeAway, Club|null, Team|null}
     */
    private function parseDetail(string $detail): array
    {
        if (in_array(strtolower(trim($detail)), ['training', 'skills session'])) {
            return ['Training', Competition::Training, HomeAway::Home, null, null];
        }

        // is CB or Pathway
        if (
            str_starts_with(trim($detail), 'CB')
            || str_contains(strtolower(trim($detail)), 'pathway')
            || str_contains(strtolower(trim($detail)), 'academy')
        ) {
            return [ucwords($detail), Competition::Pathway, HomeAway::TBA, null, null];
        }
        // is county cup / colts cup
        if (
            str_starts_with(strtolower(trim($detail)), 'county cup')
            || str_contains(strtolower(trim($detail)), 'colts cup')
            || str_contains(strtolower(trim($detail)), 'norfolk finals')
            || str_contains(strtolower(trim($detail)), 'norfolk cup')
            || str_contains(strtolower(trim($detail)), 'cup semi')
            || str_contains(strtolower(trim($detail)), 'cup final')
        ) {
            return [ucwords($detail), Competition::CountyCup, HomeAway::TBA, null, null];
        }
        // is festival
        if (str_contains(strtolower(trim($detail)), 'festival')) {
            return [ucwords($detail), Competition::Festival, HomeAway::TBA, null, null];
        }
        // is national cup
        if (str_contains(strtolower(trim($detail)), 'nat cup')) {
            return [ucwords($detail), Competition::NationalCup, HomeAway::TBA, null, null];
        }
        // is norfolk 10s
        if (str_contains(trim($detail), 'Norfolk10s')) {
            return [$detail, Competition::Norfolk10s, HomeAway::TBA, null, null];
        }
        // is Conference
        if (str_contains(strtolower(trim($detail)), 'conference')) {
            return [ucwords($detail), Competition::Conference, HomeAway::TBA, null, null];
        }
        // is special day
        if (in_array(strtolower(trim($detail)), ['mothering sunday', 'christmas', 'easter', 'out of season'])) {
            return [ucwords($detail), Competition::None, HomeAway::TBA, null, null];
        }

        // we've got this far, we think it's a club game
        $cleanParentheses = preg_replace('/\s*\([^)]*\)/', '', $detail);
        $club = null;
        $team = null;
        if ($cleanParentheses) {
            list($club, $team) = $this->findClubAndOpponent($cleanParentheses);
        }

        return [
            ucwords($detail),
            Competition::Friendly,
            $this->isHomeOrAway($detail),
            $club,
            $team,
        ];
    }

    private function isHomeOrAway(string $detail): HomeAway
    {
        if (str_contains($detail, '(H)')) {
            return HomeAway::Home;
        }
        if (str_contains($detail, '(A)')) {
            return HomeAway::Away;
        }

        return HomeAway::TBA;
    }

    /**
     * @return array<Club|Team>
     */
    private function findClubAndOpponent(string $name): array
    {
        $n = ucwords(trim(strtolower($name)));
        if (empty($n)) {
            return [null, null];
        }

        switch ($n) {
            case 'W Norfolk':
                $n = 'West Norfolk';
                break;
            case 'N Walsham':
                $n = 'North Walsham';
                break;
        }

        //        $this->io->info(sprintf('Searching for club: "%s"', $n));
        $club = $this->clubRepository->findOneBy(['name' => $n]);
        if (null == $club) {
            $club = new Club();
            $club->setName($n);
            //            $this->io->info(sprintf('Creating club for "%s"', $n));
            $this->em->persist($club);
            $this->em->flush();
        }

        return [$club, null];
    }
}
