<?php

namespace App\Service;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MagicLinkService
{
    protected LoginLinkHandlerInterface $loginLinkHandler;
    private MailerInterface $mailer;
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ) {
        $this->loginLinkHandler = $loginLinkHandler;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMagicLink(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user) {
            $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
            $loginLink = $loginLinkDetails->getUrl();
            $this->logger->debug('Login link', ['loginLink' => $loginLink, 'email' => $user->getEmail()]);
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@norwichrugby.com', 'Norwich Rugby admin bot'))
                ->to((string) $user->getEmail())
                ->subject('Login to NRFC Fixture')
                ->text(sprintf('Follow this link to login automatically: %s', $loginLink))
                ->htmlTemplate('login/magic_login_link_email.html.twig')
                ->context([
                    'loginLink' => $loginLink,
                ]);
            $this->mailer->send($email);

            return true;
        }

        return false;
    }
}
