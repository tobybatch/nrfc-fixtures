<?php
// src/Command/ImportCsvCommand.php

namespace App\Command;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Repository\ClubRepository;
use App\Service\FixtureService;
use DateTime;
use DateTimeImmutable;
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
    description: 'Import data from CSV file and create entities'
)]
class NrfcFixturesImportCommand extends Command
{
    private ObjectManager $em;
    private FixtureService $fixtureService;

    public function __construct(
        EntityManagerInterface $em,
        FixtureService $clubRepository
    )
    {
        parent::__construct();
        $this->em = $em;
        $this->fixtureService = $clubRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import data from CSV file and create entities')
            ->setHelp('This command allows you to import fixture data from a CSV file and create corresponding entities in the database.')
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

        // Skip validation for help command
        if ($input->hasOption('help') && $input->getOption('help')) {
            return Command::SUCCESS;
        }

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
        if (empty($row[0]) || !DateTime::createFromFormat('j-M-y', $row[0])) {
            return;
        }

        $date = DateTimeImmutable::createFromMutable(
            DateTime::createFromFormat('j-M-y', $row[0])
        )->setTime(0, 1, 0);

        $this->fixtureService->createFixture(Team::Minis, $date, $row[2]);
        $this->fixtureService->createFixture(Team::U13B, $date, $row[3]);
        $this->fixtureService->createFixture(Team::U14B, $date, $row[4]);
        $this->fixtureService->createFixture(Team::U15B, $date, $row[5]);
        $this->fixtureService->createFixture(Team::U16B, $date, $row[6]);
        $this->fixtureService->createFixture(Team::U18B, $date, $row[7]);
        $this->fixtureService->createFixture(Team::U12G, $date, $row[9]);
        $this->fixtureService->createFixture(Team::U14G , $date, $row[10]);
        $this->fixtureService->createFixture(Team::U16G, $date, $row[11]);
        $this->fixtureService->createFixture(Team::U18G, $date, $row[12]);
    }


}