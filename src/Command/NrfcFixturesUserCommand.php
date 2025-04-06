<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'nrfc:fixtures:user',
    description: 'Create or update a site user',
)]
class NrfcFixturesUserCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        parent::__construct();
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email address of the new user')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role to add to the new user')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $roles = [];
        if ($input->getArgument('role')) {
            $role = $input->getArgument('role');
            $io->info('Role ' . json_encode($role));
            if ($role === 'EDITOR' || $role === 'ADMIN') {
                $roles[] = 'EDITOR';
            }
            if ($role === 'ADMIN') {
                $roles[] = 'ADMIN';
            }
        }
            $io->info('Roles ' . implode(', ', $roles));

        $user = $this->userRepository->findOneByEmail($email);
        if (!$user) {
            $io->info('Creating a new user with email of ' . $email);
            $user = new User();
            $user->setEmail($email);
            $user->setPassword(sha1(rand()));
        }
        if ($user) {
            $io->info('Updating user with email of ' . $email);
            $user->setRoles($roles);
        }
        $this->em->persist($user);
        $this->em->flush();

        $io->success('Set roles on ' . $email . ' to ' . implode(',', $roles));

        return Command::SUCCESS;
    }
}
