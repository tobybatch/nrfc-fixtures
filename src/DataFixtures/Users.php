<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Users extends Fixture
{
    public const string BASIC_USER = 'basic_user@example.com';
    public const string ADMIN_USER = 'admin_user@example.com';
    public const string EDITOR_USER = 'editor_user@example.com';
    public const string PASSWORD = 'super_nutty_123';
    private UserPasswordHasherInterface $passwordHashTool;

    public function __construct(UserPasswordHasherInterface $passwordHashTool)
    {
        $this->passwordHashTool = $passwordHashTool;
    }

    public function load(ObjectManager $manager): void
    {
        $this->makeUser($manager, self::BASIC_USER);
        $this->makeUser($manager, self::ADMIN_USER, ['ROLE_ADMIN']);
        $this->makeUser($manager, self::EDITOR_USER, ['ROLE_EDITOR']);
    }

    /**
     * @param string[] $roles
     */
    private function makeUser(ObjectManager $manager, string $email, array $roles = []): void
    {
        $u = new User();
        $u->setEmail($email);
        $u->setRoles($roles);
        $u->setPassword($this->passwordHashTool->hashPassword($u, self::PASSWORD));
        $manager->persist($u);
        $manager->flush();
    }
}
