<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Users extends Fixture
{
    const BASIC_USER = 'basic_user@example.com';
    const ADMIN_USER = 'admin_user@example.com';
    const EDITOR_USER = 'editor_user@example.com';
    const PASSWORD = 'super_nutty_123';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->makeUser($manager, self::BASIC_USER);
        $this->makeUser($manager, self::ADMIN_USER, ['ROLE_ADMIN']);
        $this->makeUser($manager, self::EDITOR_USER, ['ROLE_EDITOR']);
    }

    private function makeUser(ObjectManager $manager, string $email, array $roles = []): void
    {
        $u = new User();
        $u->setEmail($email);
        $u->setRoles($roles);
        $u->setPassword($this->passwordHasher->hashPassword($u, self::PASSWORD));
        $manager->persist($u);
        $manager->flush();
    }
}