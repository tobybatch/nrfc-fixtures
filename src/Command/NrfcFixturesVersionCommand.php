<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'nrfc:fixtures:version')]
final class NrfcFixturesVersionCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Receive version information')
            ->setHelp('This command allows you to fetch various version information about the app.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $version = '0.0.1';

        if ($input->hasOption('help') && $input->getOption('help')) {
            $io->writeln('Receive version information');
            $io->writeln('This command allows you to fetch various version information about the app');

            return Command::SUCCESS;
        }

        $io->writeln($version);

        return Command::SUCCESS;
    }
}
