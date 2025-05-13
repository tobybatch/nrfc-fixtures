<?php

namespace App\Command;

use App\DataFixtures\Clubs;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nrfc:fixtures:initclubs',
    description: 'Add a short description for your command',
)]
class NrfcFixturesInitclubsCommand extends Command
{
    private ObjectManager $em;
    private ClubRepository $clubRepository;

    public function __construct(EntityManagerInterface $em, ClubRepository $clubRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->clubRepository = $clubRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Initialise clubs from statics and create entities')
            ->setHelp('This command allows you to initialise clubs from statics and create corresponding entities in the database. It will replace existing clubs unless the overwrite option is set.')
            ->addOption('overwrite', 'o', InputOption::VALUE_OPTIONAL, 'Update existing clubs by overwriting existing fields if the club already exists.', ',');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $overwrite = $input->getOption('overwrite');

        $cnt = 0;
        foreach (Clubs::CLUBS as $club) {
            $c = $this->clubRepository->findOneBy(['name' => $club['name']]);
            if (null == $c) {
                $c = new Club();
            } elseif (!$overwrite) {
                $io->warning(sprintf('Deleting existing club %s', $club['name']));
                $this->em->detach($c);
                $this->em->flush();
                $c = new Club();
            }

            $c->setName($club['name']);
            $c->setAddress($club['addr']);
            $c->setLatitude($club['lat']);
            $c->setLongitude($club['lon']);
            $this->em->persist($c);
            $io->info(sprintf('Creating club %s', $club['name']));
            ++$cnt;
        }
        $this->em->flush();

        $io->success(">$cnt clubs added.");

        return Command::SUCCESS;
    }
}
