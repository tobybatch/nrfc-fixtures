<?php
// src/Command/ImportCsvCommand.php

namespace App\Command;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Entity\Team;
use App\Repository\TeamRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use App\Entity\Fixture as FixtureEntity;

#[AsCommand(
    name: 'nrfc:fixtures:import',
    description: 'Add a short description for your command',
)]
class NrfcFixturesImportCommand extends Command
{

    private ObjectManager $em;
    private TeamRepository $teamRepository;

    public function __construct(EntityManagerInterface $em, TeamRepository $teamRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->teamRepository = $teamRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import data from CSV file and create entities')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file')
            ->addOption('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'CSV delimiter', ',')
            ->addOption('skip-first', 's', InputOption::VALUE_NONE, 'Skip first row (header)')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Flush batch size', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $delimiter = $input->getOption('delimiter');
        $skipFirstRow = $input->getOption('skip-first');
        $batchSize = (int)$input->getOption('batch-size');

        // Validate file exists
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(sprintf('File "%s" not found', $filePath));
        }

        $io->title(sprintf('Starting import from %s', $filePath));

        $file = fopen($filePath, 'r');
        if ($file === false) {
            $io->error('Could not open the file');
            return Command::FAILURE;
        }

        if ($skipFirstRow) {
            fgetcsv($file, 0, $delimiter);
        }

        $importedCount = 0;
        $rowNumber = $skipFirstRow ? 1 : 0;

        try {
            while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
                $rowNumber++;

                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $this->processRow($row);

                    $importedCount++;

                    // Batch processing
                    if ($importedCount % $batchSize === 0) {
                        $this->em->flush();
                        $this->em->clear(); // Detaches all objects from Doctrine
                        $io->comment(sprintf('Processed %d records', $importedCount));
                    }
                } catch (Exception $e) {
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
        } catch (Exception $e) {
            $io->error(sprintf('Import failed: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * Creates an entity from a CSV row
     *
     * @param array $row
     * @throws Exception When row data is invalid
     */
    private function processRow(array $row): void
    {
        $date = DateTimeImmutable::createFromMutable(DateTime::createFromFormat('j-M-y', $row[0]));
        if (!$date) {
            throw new Exception(sprintf('Invalid date format: %s', $row[0]));
        }

        $this->createFixture("Minis", $date, $row[2]);
        $this->createFixture("U13B", $date, $row[3]);
        $this->createFixture("U14B", $date, $row[4]);
        $this->createFixture("U15B", $date, $row[5]);
        $this->createFixture("U16B", $date, $row[6]);
        $this->createFixture("Colts", $date, $row[7]);
        $this->createFixture("U12G", $date, $row[9]);
        $this->createFixture("U14G", $date, $row[10]);
        $this->createFixture("U16G", $date, $row[11]);
        $this->createFixture("U18G", $date, $row[12]);
    }

    private function createFixture(string $name, DateTimeImmutable $date, mixed $detail): void
    {
        $team = $this->teamRepository->findOneBy(['name' => $name]);
        if (!$team) {
            $team = new Team();
            $team->setName($name);
            $this->em->persist($team);
            $this->em->flush();
        }
        $fixture = new FixtureEntity();
        list($sessionName, $comp, $home) = $this->parseDetail($detail);
        $fixture->setName($sessionName);
        $fixture->setCompetition($comp);
        $fixture->setHomeAway($home);
        $fixture->setDate($date);
        $fixture->setTeam($team);

        $this->em->persist($fixture);
    }

    private function parseDetail(string $detail)
    {
        if (strtolower(trim($detail)) === 'training') {
            return ["Training", Competition::None, HomeAway::Home];
        }

        // is CB or Pathway
        if (str_starts_with(trim($detail), "CB") || str_contains(trim($detail), "pathway") || str_contains(trim($detail), "academy")) {
            return [$detail, Competition::Pathway, HomeAway::TBA];
        }
        // is county cup / colts cup
        if (str_starts_with(trim($detail), "county cup") || str_contains(trim($detail), "colts cup")) {
            return [$detail, Competition::CountyCup, HomeAway::TBA];
        }
        // is festival
        if (str_contains(trim($detail), "festival")) {
            return [$detail, Competition::Festival, HomeAway::TBA];
        }
        // is nat cup
        if (str_contains(trim($detail), "nat cup")) {
            return [$detail, Competition::NationalCup, HomeAway::TBA];
        }
        // is norfolk 10s
        if (str_contains(trim($detail), "Norfolk10s")) {
            return [$detail, Competition::Norfolk10s, HomeAway::TBA];
        }
        // is Conference
        if (str_contains(trim($detail), "conference")) {
            return [$detail, Competition::Conference, HomeAway::TBA];
        }

        return [
            preg_replace('/\s*\([^)]*\)/', '', $detail),
            Competition::None,
            $this->isHomeOrAway($detail)
        ];
    }

    private function isHomeOrAway($detail): HomeAway
    {
        if (str_contains($detail, '(H)')) {
            return HomeAway::Home;
        }
        if (str_contains($detail, '(A)')) {
            return HomeAway::Away;
        }
        return HomeAway::TBA;
    }
}