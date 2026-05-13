<?php

namespace App\Command;

use App\Entity\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nrfc:fixtures:remove',
    description: 'Remove all fixtures from the database',
)]
class NrfcFixturesRemoveCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the removal without confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            if (!$io->confirm('Are you sure you want to remove all fixtures?', false)) {
                $io->warning('Action cancelled.');

                return Command::SUCCESS;
            }
        }

        $io->section('Removing all fixtures');

        try {
            $count = $this->entityManager->createQuery('DELETE FROM App\Entity\Fixture f')->execute();

            $io->success(sprintf('Successfully removed %d fixtures.', $count));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('An error occurred while removing fixtures: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
