<?php

namespace App\Tests\Controller;

use App\Controller\ProfileController;
use App\Entity\User;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProfileControllerTest extends TestCase
{
    private ProfileController $controller;
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private UserPasswordHasherInterface $passwordHasher;
    private TokenStorageInterface $tokenStorage;
    private FlashBagInterface $flashBag;
    private UrlGeneratorInterface $urlGenerator;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->controller = new ProfileController();
        
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
                ['security.password_hasher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->passwordHasher],
                ['security.token_storage', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->tokenStorage],
                ['session.flash_bag', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->flashBag],
                ['router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator],
            ]);
    }

    public function testIndex(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->initialize([], [
            'profile_form' => [
                'email' => 'test@example.com',
                'currentPassword' => 'current_password',
                'newPassword' => 'new_password',
            ]
        ]);

        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('hashed_current_password');

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(ProfileFormType::class, $user)
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

        $form->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function($field) use ($form) {
                $form->expects($this->once())
                    ->method('getData')
                    ->willReturn(match($field) {
                        'currentPassword' => 'current_password',
                        'newPassword' => 'new_password',
                        'email' => 'test@example.com',
                    });
                return $form;
            });

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'current_password')
            ->willReturn(true);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, 'new_password')
            ->willReturn('hashed_new_password');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->flashBag->expects($this->once())
            ->method('add')
            ->with('success', 'Your profile has been updated successfully!');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_profile')
            ->willReturn('/user/profile');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('profile/index.html.twig', [
                'profileForm' => $form->createView(),
            ])
            ->willReturn('rendered content');

        $response = $this->controller->index($request, $this->entityManager, $this->passwordHasher);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testIndexInvalidPassword(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->initialize([], [
            'profile_form' => [
                'email' => 'test@example.com',
                'currentPassword' => 'wrong_password',
                'newPassword' => 'new_password',
            ]
        ]);

        $user = new User();
        $user->setEmail('old@example.com');
        $user->setPassword('hashed_current_password');

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(ProfileFormType::class, $user)
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

        $form->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function($field) use ($form) {
                $form->expects($this->once())
                    ->method('getData')
                    ->willReturn(match($field) {
                        'currentPassword' => 'wrong_password',
                        'newPassword' => 'new_password',
                        'email' => 'test@example.com',
                    });
                return $form;
            });

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'wrong_password')
            ->willReturn(false);

        $this->flashBag->expects($this->once())
            ->method('add')
            ->with('error', 'Your current password is incorrect.');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_profile')
            ->willReturn('/user/profile');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('profile/index.html.twig', [
                'profileForm' => $form->createView(),
            ])
            ->willReturn('rendered content');

        $response = $this->controller->index($request, $this->entityManager, $this->passwordHasher);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }
} 