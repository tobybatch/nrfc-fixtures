<?php

// src/Controller/LoginController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HttpException;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

#[Route('/user')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        // TODO - pass this in
        // $lastUsername = $authenticationUtils->getLastUsername();

        $loginForm = $this->createForm(LoginFormType::class);

        return $this->render('login/index.html.twig', [
            'hide_top_login' => true,
            'loginForm' => $loginForm,
            'error' => $error,
        ]);
    }

    #[Route('/login_check', name: 'login_check')]
    public function check(): never
    {
        throw new LogicException('This code should never be reached');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/magic_login', name: 'app_magic_login')]
    public function requestLoginLink(
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

            if ($user) {

                $loginLinkDetails = $loginLinkHandler->createLoginLink($user);
                $loginLink = $loginLinkDetails->getUrl();
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@norwichrugby.com', 'Norwich Rugby admin bot'))
                    ->to((string)$user->getEmail())
                    ->subject('Login to NRFC Fixture')
                    ->text(sprintf('Follow this link to login automatically: %s', $loginLink))
                    ->htmlTemplate('login/magic_login_link_email.html.twig')
                    ->context([
                        'loginLink' => $loginLink,
                    ]);

                $mailer->send($email);

                $this->addFlash(
                    'success', // The type (can be anything: success, error, warning, etc.)
                    'Check your email for a magic login link' // The message
                );

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash(
                    'error', // The type (can be anything: success, error, warning, etc.)
                    'Unknown email address' // The message
                );
            }
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('login/request_login_link.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHashTool,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHashTool->hashPassword($user, $plainPassword));

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
