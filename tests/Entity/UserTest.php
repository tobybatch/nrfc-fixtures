<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTest extends TestCase
{
    private User $user;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
    }

    public function testGetId(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testGetEmail(): void
    {
        $this->assertNull($this->user->getEmail());
    }

    public function testSetEmail(): void
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getEmail());
    }

    public function testGetUserIdentifier(): void
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testGetRoles(): void
    {
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
    }

    public function testSetRoles(): void
    {
        $roles = ['ROLE_ADMIN'];
        $this->user->setRoles($roles);
        $this->assertEquals($roles, $this->user->getRoles());
    }

    public function testGetPassword(): void
    {
        $this->assertNull($this->user->getPassword());
    }

    public function testSetPassword(): void
    {
        $password = 'password123';
        $this->user->setPassword($password);
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testGetPlainPassword(): void
    {
        $this->assertNull($this->user->getPlainPassword());
    }

    public function testSetPlainPassword(): void
    {
        $plainPassword = 'password123';
        $this->user->setPlainPassword($plainPassword);
        $this->assertEquals($plainPassword, $this->user->getPlainPassword());
    }

    public function testEraseCredentials(): void
    {
        $this->user->setPlainPassword('password123');
        $this->user->eraseCredentials();
        $this->assertNull($this->user->getPlainPassword());
    }

    public function testIsVerified(): void
    {
        $this->assertFalse($this->user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $this->user->setIsVerified(true);
        $this->assertTrue($this->user->isVerified());
    }
} 