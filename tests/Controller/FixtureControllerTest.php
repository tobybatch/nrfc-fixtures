<?php

namespace App\Tests\Controller;

use App\Config\Team;
use App\Controller\FixtureController;
use App\Entity\Fixture;
use App\Repository\FixtureRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Config\Competition;
use App\Config\HomeAway;

class FixtureControllerTest extends TestCase
{
    private FixtureController $controller;
    private FixtureRepository $fixtureRepository;
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private UrlGeneratorInterface $urlGenerator;
    private \Symfony\Bundle\SecurityBundle\Security $security;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->fixtureRepository = $this->createMock(FixtureRepository::class);
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->security = $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);

        $this->controller = new FixtureController($this->fixtureRepository);
        
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
                ['security.helper', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->security],
                ['router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator],
            ]);
    }

    public function testIndex(): void
    {
        $request = new Request();
        $date = '2023-01-01';
        $team = Team::Minis;

        $this->fixtureRepository->expects($this->once())
            ->method('getDates')
            ->willReturn([$date]);

        $this->fixtureRepository->expects($this->exactly(count(Team::cases())))
            ->method('getFixturesForTeam')
            ->willReturn([]);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('fixture/index.html.twig', [
                'teams' => Team::cases(),
                'fixtures' => [
                    $date => array_combine(
                        array_map(fn($team) => $team->value, Team::cases()),
                        array_fill(0, count(Team::cases()), [])
                    ),
                ],
            ])
            ->willReturn('rendered content');

        $response = $this->controller->index($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testNew(): void
    {
        $request = new Request();
        $fixture = new Fixture();
        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('App\Form\FixtureType', $fixture)
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
            ->with($fixture);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_fixture_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/');

        $response = $this->controller->new($request, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function testShow(): void
    {
        $fixture = new Fixture();
        // Use reflection to set the id property
        $reflection = new \ReflectionClass($fixture);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($fixture, 1);

        // Set required properties
        $fixture->setCompetition(Competition::Conference);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setTeam(Team::Minis);
        $fixture->setDate(new \DateTimeImmutable());

        $this->twig->expects($this->once())
            ->method('render')
            ->with('fixture/show.html.twig', [
                'fixture' => $fixture,
            ])
            ->willReturn('rendered content');

        $response = $this->controller->show($fixture);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testEdit(): void
    {
        $request = new Request();
        $fixture = new Fixture();
        // Use reflection to set the id property
        $reflection = new \ReflectionClass($fixture);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($fixture, 1);

        // Set required properties
        $fixture->setCompetition(Competition::Conference);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setTeam(Team::Minis);
        $fixture->setDate(new \DateTimeImmutable());

        $form = $this->createMock(FormInterface::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with('App\Form\FixtureType', $fixture)
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
            ->with('app_fixture_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/');

        $response = $this->controller->edit($request, $fixture, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function testDelete(): void
    {
        $request = new Request();
        $request->setMethod('POST');
        $request->request->set('_token', 'valid_token');

        $fixture = new Fixture();
        // Use reflection to set the id property
        $reflection = new \ReflectionClass($fixture);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($fixture, 1);

        // Set required properties
        $fixture->setCompetition(Competition::Conference);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setTeam(Team::Minis);
        $fixture->setDate(new \DateTimeImmutable());

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('app_fixture_index', [], Response::HTTP_SEE_OTHER)
            ->willReturn('/');

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($fixture);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $response = $this->controller->delete($request, $fixture, $this->entityManager);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }

    public function testByDate(): void
    {
        $date = '20240101';
        $dateObj = \DateTimeImmutable::createFromFormat('Ymd', $date);
        $startOfDay = $dateObj->setTime(0, 0, 0);
        $endOfDay = $dateObj->setTime(23, 59, 59);

        $fixture = new Fixture();
        // Set required properties
        $fixture->setCompetition(Competition::Conference);
        $fixture->setHomeAway(HomeAway::Home);
        $fixture->setTeam(Team::Minis);
        $fixture->setDate($dateObj);

        $this->fixtureRepository->expects($this->once())
            ->method('findByDateRange')
            ->with($startOfDay, $endOfDay)
            ->willReturn([$fixture]);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('fixture/byDate.html.twig', [
                'date' => $dateObj->format('Y-m-d'),
                'fixtures' => [
                    'TRAINING' => [],
                    'HOME' => [$fixture],
                    'AWAY' => [],
                    'TBA' => [],
                ],
            ])
            ->willReturn('rendered content');

        $response = $this->controller->byDate($date, $this->fixtureRepository);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered content', $response->getContent());
    }

    public function testByDateInvalidFormat(): void
    {
        $date = 'invalid-date';

        $response = $this->controller->byDate($date, $this->fixtureRepository);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
} 