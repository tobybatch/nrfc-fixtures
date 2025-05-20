<?php

// src/Controller/LoginController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Service\MagicLinkService;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/user')]
class UserController extends AbstractController
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
        Request          $request,
        MagicLinkService $magicLinkService): Response
    {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $email = $request->getPayload()->get('email');

            if ($magicLinkService->sendMagicLink($email)) {
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
                return $this->redirectToRoute('app_magic_login');
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
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHashTool,
        EntityManagerInterface      $entityManager,
        MailerInterface             $mailer,
    ): Response
    {
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
                ->to((string)$user->getEmail())
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

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/updatePreferences', name: 'app_update_preferences', methods: ['POST'])]
    public function updatePreferences(
        Request         $request,
        LoggerInterface $logger,
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        if (in_array('showHelp', array_keys($data))) {
            $key = $data['showHelp']["route"];
            $value = $data['showHelp']["state"];

            /* @var User $user */
            $user = $this->getUser();

            $preferences = ['showHelp' => [$key => $value]];
            $cookie_preferences = $request->cookies->get('preferences');
            if ($cookie_preferences) {
                $preferences = array_merge(
                    json_decode($cookie_preferences, true),
                    $preferences,
                );
            }
            if ($user instanceof User) {
                $preferences = array_merge(
                    $user->getPreferences(),
                    $preferences,
                );
                $user->setPreferences($preferences);
            }
            $request->getSession()->set('preferences', $preferences);
            $logger->warning(
                sprintf(
                    'Preferences updated: %s',
                    json_encode($preferences)
                )
            );

            return new JsonResponse($preferences);
        }
        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
}
