<?php

namespace App\Service;

use App\Repository\UserRepository;
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

    public function __construct(
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        MailerInterface $mailer,
    ) {
        $this->loginLinkHandler = $loginLinkHandler;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
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
