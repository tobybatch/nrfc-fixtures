<?php

// src/Command/ImportCsvCommand.php

namespace App\Command;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Controller\AdminController;
use App\Entity\Club;
use App\Entity\Fixture as FixtureEntity;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use App\Service\ImportExportService;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'nrfc:fixtures:import',
    description: 'Import data from CSV file and create entities'
)]
class NrfcFixturesImportCommand extends Command
{
    public function __construct(
        private readonly ImportExportService $importExportService,
        private readonly KernelInterface        $kernel,
        private readonly ParameterBagInterface  $bag,)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import fixture data from CSV file and create entities')
            ->setHelp('This command allows you to import fixture data from a CSV file and create corresponding entities in the database.')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        // Validate file exists
        if (!file_exists($filePath)) {
            throw new FileNotFoundException(sprintf('File "%s" not found', $filePath));
        }

        $io->title(sprintf('Starting import from %s', $filePath));

        try {
            $clubsSrc = $this->kernel->getProjectDir() . '/' . $this->bag->get('asset_path_clubs');
            $handle = fopen($clubsSrc, 'r+');
            $this->importExportService->readClubsFromCsvResource($handle);
            fclose($handle);

            $file = fopen($filePath, 'r');
            $ioDto = $this->importExportService->importFixtures($file);
            fclose($file);

            foreach ($ioDto->getErrors() as $error) {
                $io->error($error);
            }
            if ($ioDto->getSuccessCount()) {
                $io->success(sprintf('Successfully imported %d records', $ioDto->getSuccessCount()));
            }
            if ($ioDto->getUpdateCount()) {
                $io->success(sprintf('Successfully updated %d records', $ioDto->getUpdateCount()));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Import failed: %s', $e->getMessage()));
            $io->info($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
