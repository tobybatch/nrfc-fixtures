<?php

namespace App\Tests\Controller;

use App\Controller\LoginController;
use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginControllerTest extends TestCase
{
    private LoginController $controller;
    private AuthenticationUtils $authenticationUtils;
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;
    private EmailVerifier $emailVerifier;
    private MailerInterface $mailer;
    private NotifierInterface $notifier;
    private LoginLinkHandlerInterface $loginLinkHandler;
    private UserRepository $userRepository;
    private TokenStorageInterface $tokenStorage;
    private FlashBagInterface $flashBag;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->authenticationUtils = $this->createMock(AuthenticationUtils::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->loginLinkHandler = $this->createMock(LoginLinkHandlerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->controller = new LoginController();
        
        // Set the container using reflection
        $reflection = new \ReflectionClass($this->controller);
        $containerProperty = $reflection->getProperty('container');
        $containerProperty->setAccessible(true);
        $containerProperty->setValue($this->controller, $this->container);

        $this->container->method('get')
            ->willReturnMap([
                ['doctrine.orm.entity_manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
                ['form.factory', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->formFactory],
                ['twig', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->twig],
                ['security.authentication_utils', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->authenticationUtils],
                ['security.password_hasher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->passwordHasher],
            ]);
    }

    public function testIndex(): void
    {
        $error = null;
        $lastUsername = 'test@example.com';
        $loginForm = $this->createMock(FormInterface::class);

        $this->authenticationUtils->expects($this->once())
            ->method('getLastAuthenticationError')
            ->willReturn($error);

        $this->authenticationUtils->expects($this->once())
            ->method('getLastUsername')
            ->willReturn($lastUsername);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(LoginFormType::class, null)
            ->willReturn($loginForm);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('login/index.html.twig', [
                'controller_name' => 'LoginController',
                'loginForm' => $loginForm,
                'error' => $error,
            ])
            ->willReturn('rendered content');

        $response = $this->controller->index($this->authenticationUtils);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testRequestLoginLink(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->initialize([], [], [], [], [], [], ['email' => 'test@example.com']);

        $user = new User();
        $user->setEmail('test@example.com');

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $loginLinkDetails = $this->createMock(\Symfony\Component\Security\Http\LoginLink\LoginLinkDetails::class);
        $loginLinkDetails->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://example.com/login-link');

        $this->loginLinkHandler->expects($this->once())
            ->method('createLoginLink')
            ->with($user)
            ->willReturn($loginLinkDetails);

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function($email) {
                return $email instanceof TemplatedEmail &&
                    $email->getSubject() === 'Login to NRFC Fixture' &&
                    $email->getHtmlTemplate() === 'login/magic_login_link_email.html.twig';
            }));

        $this->twig->expects($this->once())
            ->method('render')
            ->with('login/login_link_sent.html.twig')
            ->willReturn('rendered content');

        $response = $this->controller->requestLoginLink(
            $this->notifier,
            $this->loginLinkHandler,
            $this->userRepository,
            $request,
            $this->mailer
        );
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testRegister(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('registration_form', [
            'email' => 'test@example.com',
            'plainPassword' => 'password123',
        ]);

        $user = new User();
        $user->setEmail('test@example.com');

        $form = $this->createMock(FormInterface::class);
        $plainPasswordForm = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(RegistrationFormType::class, $this->callback(function($u) {
                return $u instanceof User;
            }))
            ->willReturn($form);

        $form->expects($this->once())
            ->method('handleRequest')
            ->with($request);

        $form->expects($this->once())
            ->method('isSubmitted')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $form->expects($this->once())
            ->method('getData')
            ->willReturn($user);

        $form->expects($this->once())
            ->method('get')
            ->with('plainPassword')
            ->willReturn($plainPasswordForm);

        $plainPasswordForm->expects($this->once())
            ->method('getData')
            ->willReturn('password123');

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->callback(function($u) {
                return $u instanceof User && $u->getEmail() === 'test@example.com';
            }), 'password123')
            ->willReturn('hashed_password');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function($persistedUser) {
                return $persistedUser instanceof User &&
                    $persistedUser->getEmail() === 'test@example.com';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function($email) {
                return $email instanceof TemplatedEmail &&
                    $email->getSubject() === 'Welcome to NRFC Fixture' &&
                    $email->getHtmlTemplate() === 'login/confirmation_email.html.twig';
            }));

        $this->twig->expects($this->once())
            ->method('render')
            ->with('login/register.html.twig', [
                'registrationForm' => $form,
            ])
            ->willReturn('rendered content');

        $response = $this->controller->register(
            $request,
            $this->passwordHasher,
            $this->entityManager,
            $this->mailer
        );
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }
} 