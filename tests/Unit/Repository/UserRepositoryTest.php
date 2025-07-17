<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private MockObject $managerRegistry;
    private MockObject $entityManager;
    private MockObject $classMetadata;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->classMetadata = $this->createMock(ClassMetadata::class);

        // Mock ClassMetadata to avoid uninitialized property access
        $this->classMetadata->name = User::class;

        // Configure EntityManager to return the mocked ClassMetadata
        $this->entityManager
            ->method('getClassMetadata')
            ->with(User::class)
            ->willReturn($this->classMetadata);

        // Configure ManagerRegistry to return the mocked EntityManager
        $this->managerRegistry
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($this->entityManager);

        $this->userRepository = new UserRepository($this->managerRegistry);
    }

    public function testUpgradePasswordWithValidUser(): void
    {
        $user = new User();
        $newHashedPassword = 'new_hashed_password';

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->userRepository->upgradePassword($user, $newHashedPassword);

        $this->assertSame($newHashedPassword, $user->getPassword());
    }

    public function testUpgradePasswordWithInvalidUserThrowsException(): void
    {
        $invalidUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $newHashedPassword = 'new_hashed_password';

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage(sprintf('Instances of "%s" are not supported.', get_class($invalidUser)));

        $this->userRepository->upgradePassword($invalidUser, $newHashedPassword);
    }
}
