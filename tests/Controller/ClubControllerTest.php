<?php

namespace App\Tests\Controller;

use App\Controller\ClubController;
use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Templating\EngineInterface;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClubControllerTest extends TestCase
{
    private ClubController $controller;
    private ClubRepository $clubRepository;
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private Security $security;
    private UrlGeneratorInterface $urlGenerator;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->clubRepository = $this->createMock(ClubRepository::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->controller = new ClubController();
        
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
                ['router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator],
            ]);
    }

    public function testIndex(): void
    {
        $clubs = [new Club()];
        $this->clubRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($clubs);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('club/index.html.twig', ['clubs' => $clubs])
            ->willReturn('rendered content');

        $response = $this->controller->index($this->clubRepository);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testNew(): void
    {
        $request = new Request();
        $club = new Club();
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('App\Form\ClubType', $club)
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

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($club);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_club_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/club');

        $response = $this->controller->new($request, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/club', $response->getTargetUrl());
    }

    public function testShow(): void
    {
        $club = new Club();
        $club->setName('Test Club');
        $club->setLatitude(52.6309);
        $club->setLongitude(1.2974);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('club/show.html.twig', $this->callback(function($parameters) {
                return isset($parameters['club']) && isset($parameters['map']);
            }))
            ->willReturn('rendered content');

        $response = $this->controller->show($club);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testEdit(): void
    {
        $request = new Request();
        $club = new Club();
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('App\Form\ClubType', $club)
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

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_club_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/club');

        $response = $this->controller->edit($request, $club, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/club', $response->getTargetUrl());
    }

    public function testDelete(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('_token', 'valid_token');

        $club = new Club();
        // Use reflection to set the id property
        $reflection = new \ReflectionClass($club);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($club, 1);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_club_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/club');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($club);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($request, $club, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/club', $response->getTargetUrl());
    }
} 