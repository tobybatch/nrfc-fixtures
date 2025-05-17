<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertSame('test@example.com', $user->getUserIdentifier());

        $userNoEmail = new User();
        $reflection = new \ReflectionClass($userNoEmail);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($userNoEmail, 42);
        $this->assertSame('User 42', $userNoEmail->getUserIdentifier());
    }

    public function testRoles(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());

        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(2, $roles); // no duplicates
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('securehashedpassword');
        $this->assertSame('securehashedpassword', $user->getPassword());
    }

    public function testPreferences(): void
    {
        $user = new User();
        $prefs = ['theme' => 'dark', 'notifications' => true];
        $user->setPreferences($prefs);
        $this->assertSame($prefs, $user->getPreferences());
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        // Just call to ensure no exceptions — logic may be expanded in the future
        $user->eraseCredentials();
        $this->assertTrue(true); // always passes unless method throws
    }
}
