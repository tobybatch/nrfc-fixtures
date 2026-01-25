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
    name: 'nrfc:fixtures:export',
    description: 'Import data from CSV file and create entities'
)]
class NrfcFixturesExportCommand extends Command
{
    public function __construct(
        private readonly FixtureRepository $fixtureRepository,
        private readonly KernelInterface        $kernel,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Export fixture data to a CSV file.');
    }

    private function fixCompetitionType(Competition $in = null): string
    {
        if (!$in) {
            return '';
        }

        return match ($in) {
            Competition::NationalCup => 'National Cup',
            Competition::CountyCup => 'County Cup',
            Competition::Norfolk10s => 'Norfolk 10s',
            default => $in->value,
        };
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting export');

        try {
            $fh = fopen($this->kernel->getProjectDir() . '/skunk/fixtures.csv', 'w+');
            fputcsv($fh, [
                'date',
                'team',
                'opposing_club',
                'opposing_team',
                'competition_type',
                'kick_off_time',
                'venue',
                'notes',
            ]);
            $allFixtures = $this->fixtureRepository->findAll();
            foreach ($allFixtures as $fixture) {
                if (! in_array($fixture->getCompetition(), [Competition::Training, Competition::None])) {
                    fputcsv($fh, [
                        $fixture->getDate()->format('Y-m-d'),
                        $fixture->getTeam()->value,
                        $fixture->getClub()?->getName(),
                        $fixture->getOpponent() ? $fixture->getOpponent()->value : '',
                        $this->fixCompetitionType($fixture->getCompetition()),
                        "",
                        HomeAway::Home == $fixture->getHomeAway() ? 'home' : 'away',
                        $fixture->getNotes(),
                    ]);
                }
            }
            fclose($fh);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Export failed: %s', $e->getMessage()));
            $io->info($e->getTraceAsString());

            return Command::FAILURE;
        }
    }
}
