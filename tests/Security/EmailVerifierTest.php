<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;

class EmailVerifierTest extends TestCase
{
    private EmailVerifier $emailVerifier;
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->verifyEmailHelper = $this->createMock(VerifyEmailHelperInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->emailVerifier = new EmailVerifier(
            $this->verifyEmailHelper,
            $this->mailer,
            $this->entityManager
        );
    }

    public function testSendEmailConfirmation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->setPrivateProperty($user, 'id', 1);

        $email = new TemplatedEmail();
        $email->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $signatureComponents = new VerifyEmailSignatureComponents(
            'http://example.com/verify',
            'signature',
            new \DateTimeImmutable('+1 hour')
        );

        $this->verifyEmailHelper->expects($this->once())
            ->method('generateSignature')
            ->with('app_verify_email', '1', 'test@example.com')
            ->willReturn($signatureComponents);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                return $email->getTo()[0]->getAddress() === 'test@example.com' &&
                       $email->getSubject() === 'Please Confirm your Email';
            }));

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
    }

    public function testHandleEmailConfirmation(): void
    {
        $request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $user = new User();
        $user->setEmail('test@example.com');
        $this->setPrivateProperty($user, 'id', 1);

        $this->verifyEmailHelper->expects($this->once())
            ->method('validateEmailConfirmationFromRequest')
            ->with($request, '1', 'test@example.com');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    public function testHandleEmailConfirmationThrowsException(): void
    {
        $request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $user = new User();
        $user->setEmail('test@example.com');
        $this->setPrivateProperty($user, 'id', 1);

        $this->verifyEmailHelper->expects($this->once())
            ->method('validateEmailConfirmationFromRequest')
            ->with($request, '1', 'test@example.com')
            ->willThrowException(new \Exception('Invalid signature'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid signature');

        $this->emailVerifier->handleEmailConfirmation($request, $user);
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
} 