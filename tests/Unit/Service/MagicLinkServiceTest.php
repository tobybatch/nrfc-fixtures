<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MagicLinkService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MagicLinkServiceTest extends TestCase
{
    private MockObject $loginLinkHandler;
    private MockObject $userRepository;
    private MockObject $mailer;
    private MagicLinkService $magicLinkService;

    protected function setUp(): void
    {
        $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);

        $this->magicLinkService = new MagicLinkService(
            $this->loginLinkHandler,
            $this->userRepository,
            $this->mailer
        );
    }

    public function testSendMagicLinkReturnsTrueWhenUserIsFound(): void
    {
        $email = 'test@example.com';
        $user = new User();
        $user->setEmail($email);
        $loginLink = 'http://example.com/login-link';
        $loginLinkDetails = $this->createMock(LoginLinkDetails::class);

        $this->userRepository
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        $loginLinkDetails
            ->method('getUrl')
            ->willReturn($loginLink);

        $this->loginLinkHandler
            ->method('createLoginLink')
            ->with($user)
            ->willReturn($loginLinkDetails);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) use ($loginLink, $user) {
                $this->assertEquals('no-reply@norwichrugby.com', $email->getFrom()[0]->getAddress());
                $this->assertEquals('Norwich Rugby admin bot', $email->getFrom()[0]->getName());
                $this->assertEquals($user->getEmail(), $email->getTo()[0]->getAddress());
                $this->assertEquals('Login to NRFC Fixture', $email->getSubject());
                $this->assertStringContainsString($loginLink, $email->getTextBody());
                $this->assertEquals('login/magic_login_link_email.html.twig', $email->getHtmlTemplate());
                $this->assertEquals(['loginLink' => $loginLink], $email->getContext());

                return true;
            }));

        $result = $this->magicLinkService->sendMagicLink($email);
        $this->assertTrue($result);
    }

    public function testSendMagicLinkReturnsFalseWhenUserIsNotFound(): void
    {
        $email = 'nonexistent@example.com';

        $this->userRepository
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $this->mailer
            ->expects($this->never())
            ->method('send');

        $result = $this->magicLinkService->sendMagicLink($email);
        $this->assertFalse($result);
    }
}
