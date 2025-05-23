<?php

namespace App\Controller;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Fixture;
use App\Form\FixtureType;
use App\Repository\FixtureRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/')]
final class FixtureController extends AbstractController
{
    private FixtureRepository $fixtureRepository;

    public function __construct(FixtureRepository $fixtureRepository)
    {
        $this->fixtureRepository = $fixtureRepository;
    }

    #[Route(name: 'app_fixture_index', methods: ['GET', 'POST'])]
    public function index(Request $request, SerializerInterface $serializer): Response|JsonResponse
    {
        $teams = [];

        if ($request->isMethod('GET')) {
            $team = $request->query->get('team');
            if (null !== $team) {
                $teams = [Team::getBy($team)];
            }
        }

        if (0 === count($teams)) {
            $teams = Team::cases();
        }

        $fixtures = [];
        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            $fixture = [];
            foreach ($teams as $team) {
                if ($team) {
                    $fixture[$team->value] = $this->fixtureRepository->getFixturesForTeam($team, $date);
                }
            }
            $fixtures[$date] = $fixture;
        }

        $context = [
            'teams' => $teams,
            'fixtures' => $fixtures,
        ];

        // This could be done in an event listener
        if ($request->getAcceptableContentTypes()[0] === 'application/json') {
//            $data = [];
//            foreach ($fixtures as $date => $fixturesOnDate) {
//                $data[$date] = [];
//                foreach ($fixturesOnDate as $team => $fixturesForTeams) {
//                    foreach ($fixturesForTeams as $fixture) {
//                        $data[$date][$team] = $fixture->format();
//                    }
//                }
//            }
            $json = $serializer->serialize($context, 'json');

            return new JsonResponse($json, 200, [], true);
        }

        return $this->render('fixture/index.html.twig', $context);
    }

    #[Route('/new', name: 'app_fixture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fixture = new Fixture();
        $form = $this->createForm(FixtureType::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fixture);
            $entityManager->flush();

            return $this->redirectToRoute('app_fixture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture/new.html.twig', [
            'fixture' => $fixture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fixture_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Fixture $fixture): Response
    {
        return $this->render('fixture/show.html.twig', [
            'fixture' => $fixture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fixture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FixtureType::class, $fixture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_fixture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture/edit.html.twig', [
            'fixture' => $fixture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fixture_delete', methods: ['POST'])]
    public function delete(Request $request, Fixture $fixture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fixture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fixture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_fixture_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/byDate/{date}', name: 'app_fixture_byDate', methods: ['GET'])]
    public function byDate(
        string $date,
        FixtureRepository $fixtureRepository,
    ): Response {
        // Manually validate the date format
        $dateObj = DateTimeImmutable::createFromFormat('Ymd', $date);

        if (!$dateObj || $dateObj->format('Ymd') !== $date) {
            return $this->json([
                'error' => 'Invalid date format. Please use Ymd format (e.g., 20230501 for May 1, 2023).',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Create DateTimeImmutable from the input string
            $dateObj = DateTimeImmutable::createFromFormat('Ymd', $date);

            if (!$dateObj) {
                throw new InvalidArgumentException('Invalid date');
            }

            // Set the start and end of the day
            $startOfDay = $dateObj->setTime(0, 0);
            $endOfDay = $dateObj->setTime(23, 59, 59);

            // Find fixtures between start and end of day
            $_fixtures = $fixtureRepository->findByDateRange($startOfDay, $endOfDay);
            $fixtures = [
                'TRAINING' => [],
                'HOME' => [],
                'AWAY' => [],
                'TBA' => [],
            ];
            foreach ($_fixtures as $fixture) {
                if (Competition::Training == $fixture->getCompetition() || Competition::None == $fixture->getCompetition()) {
                    $fixtures['TRAINING'][] = $fixture;
                } elseif (HomeAway::Away == $fixture->getHomeAway()) {
                    $fixtures['AWAY'][] = $fixture;
                } elseif (HomeAway::Home == $fixture->getHomeAway()) {
                    $fixtures['HOME'][] = $fixture;
                } else {
                    $fixtures['TBA'][] = $fixture;
                }
            }

            // now sort into training, home games, away games, TBA
        } catch (Exception $e) {
            return $this->json([
                'error' => 'An error occurred while processing your request.',
                'details' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->render('fixture/byDate.html.twig', [
            'date' => $dateObj->format('Y-m-d'),
            'fixtures' => $fixtures,
        ]);
    }
}
