<?php
// src/Controller/LoginController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Notifier\NRFCLoginLinkNotification;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
use Symfony\Component\Notifier\NotifierInterface;

#[Route('/user')]
class LoginController extends AbstractController
{

    public function __construct(private readonly EmailVerifier $emailVerifier)
    {
    }

    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = $this->createForm(LoginFormType::class, null);

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'loginForm' => $loginForm,
            'error' => $error,
        ]);
    }

    #[Route('/login_check', name: 'login_check')]
    public function check(): never
    {
        throw new \LogicException('This code should never be reached');
    }

    #[Route('/magic_login', name: 'app_magic_login')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        MailerInterface $mailer): Response
    {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
            $loginLink = $loginLinkDetails->getUrl();
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@norwichrugby.com', 'Norwich Rugby admin bot'))
                ->to((string) $user->getEmail())
                ->subject('Login to NRFC Fixture')
                ->text(sprintf('Follow this link to login automatically: %s', $loginLink))
                ->htmlTemplate('login/magic_login_link_email.html.twig')
                ->context([
                        'loginLink' => $loginLink
                    ]);

            $mailer->send($email);

            // render a "Login link is sent!" page
            return $this->render('login/login_link_sent.html.twig');
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('login/request_login_link.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@norwichrugby.com', 'Norwich Rugby admin bot'))
                ->to((string) $user->getEmail())
                ->subject('Welcome to NRFC Fixture')
                ->htmlTemplate('login/confirmation_email.html.twig');

            $mailer->send($email);

            // Add a flash message
            $this->addFlash(
                'success', // The type (can be anything: success, error, warning, etc.)
                'Account created, you can log in now!' // The message
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('login/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}