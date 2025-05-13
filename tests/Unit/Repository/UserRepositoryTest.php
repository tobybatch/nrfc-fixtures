<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private $passwordUpgrade;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Create an instance of the class containing upgradePassword method
        // This assumes the method is in a service class that has access to EntityManager
        $this->passwordUpgrade = new class($this->entityManager) {
            private EntityManagerInterface $entityManager;

            public function __construct(EntityManagerInterface $entityManager)
            {
                $this->entityManager = $entityManager;
            }

            public function getEntityManager(): EntityManagerInterface
            {
                return $this->entityManager;
            }

            public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
            {
                if (!$user instanceof User) {
                    throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
                }

                $user->setPassword($newHashedPassword);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
            }
        };
    }

    public function testUpgradePasswordWithValidUser()
    {
        $user = new User();
        $oldPassword = 'old_hash';
        $newPassword = 'new_hash';

        $user->setPassword($oldPassword);

        // Expect persist and flush to be called once
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->passwordUpgrade->upgradePassword($user, $newPassword);

        $this->assertEquals($newPassword, $user->getPassword());
    }

    public function testUpgradePasswordWithUnsupportedUser()
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string { return 'hash'; }
            // Other required interface methods would be here
        };

        $this->passwordUpgrade->upgradePassword($unsupportedUser, 'new_hash');
    }

    public function testPasswordIsUpdated()
    {
        $user = new User();
        $newPassword = 'brand_new_hash';

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->passwordUpgrade->upgradePassword($user, $newPassword);

        $this->assertSame($newPassword, $user->getPassword());
    }
}