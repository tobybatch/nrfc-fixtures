<?php

namespace App\Tests\Unit\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

class ResetPasswordRequestRepositoryTest extends TestCase
{
    private MockObject $registry;
    private ResetPasswordRequestRepository $repository;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new ResetPasswordRequestRepository($this->registry);
    }

    public function testCreateResetPasswordRequest(): void
    {
        $user = $this->createMock(User::class);
        $expiresAt = new \DateTimeImmutable();
        $selector = 'test-selector';
        $hashedToken = 'test-hashed-token';

        $resetPasswordRequest = $this->repository->createResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertInstanceOf(ResetPasswordRequestInterface::class, $resetPasswordRequest);
        $this->assertInstanceOf(ResetPasswordRequest::class, $resetPasswordRequest);
        $this->assertSame($user, $resetPasswordRequest->getUser());
        $this->assertSame($expiresAt, $resetPasswordRequest->getExpiresAt());
        $this->assertSame($hashedToken, $resetPasswordRequest->getHashedToken());
    }
}