<?php

namespace App\Controller;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Fixture;
use App\Form\FixtureType;
use App\Form\FixturesDisplayOptionsForm;
use App\Form\Model\FixturesDisplayOptionsDTO;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use App\Service\PreferencesService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
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
    private PreferencesService $preferencesService;
    private LoggerInterface $logger;

    public function __construct(
        FixtureRepository $fixtureRepository,
        PreferencesService $preferencesService,
        LoggerInterface $logger,
    )
    {
        $this->fixtureRepository = $fixtureRepository;
        $this->preferencesService = $preferencesService;
        $this->logger = $logger;
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route(name: 'app_fixture_index', methods: ['GET', 'POST'])]
    public function index(Request $request, SerializerInterface $serializer): Response|JsonResponse
    {
        if ($request->isMethod('GET')) {
            $team = $request->query->get('team');
            $this->logger->debug('Team param', ['team' => $team]);
            if (null !== $team) {
                $selectedTeams = [];
                switch ($team) {
                    case 'boys':
                        $selectedTeams = Team::getBoys();
                        break;
                    case 'girls':
                        $selectedTeams = Team::getGirls();
                        break;
                    case'youth':
                        $selectedTeams = Team::getYouth();
                        break;
                    case 'seniors':
                        $selectedTeams = Team::getSenior();
                        break;
                    default:
                }
                $this->preferencesService->setPreferences('teamsSelected', array_map(static fn (Team $team) => $team->value, $selectedTeams));
            }
        }

        $preferences = $this->preferencesService->getPreferences();
        $this->logger->debug('Preferences', ['preferences' => $preferences]);

        $displayOptions = new FixturesDisplayOptionsDTO();
        $_teams = $preferences['teamsSelected'] ?? [];
        $displayOptions->teams = $_teams;
        $displayOptions->showPastDates = $preferences['showPastDates'] ?? false;
        $teamsForm = $this->createForm(FixturesDisplayOptionsForm::class, $displayOptions, [
            'action' => $this->generateUrl(
                'app_preferences_update'
            ),
            'method' => 'POST',
        ]);

        $this->logger->debug('_Teams', ['_teams' => $_teams]);
        if (empty($_teams)) {
            $teams = Team::getYouth();
        } else {
            $teams = array_map(fn($team) => Team::getBy($team), $_teams);
        }

        $this->logger->debug('Teams', ['teams' => $teams]);

        $showPastDates = $this->preferencesService->getPreferences()['showPastDates'] ?? false;

        $fixtures = [];
        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            // check date is today or later, or force show is set
            if ($showPastDates || new DateTime($date) >= new DateTime()) {
                $fixturesForDate = [];
                foreach ($teams as $team) {
                    if ($team) {
                        $_fixturesForTeam = $this->fixtureRepository->getFixturesForTeam($team, $date);
                        if (!empty($_fixturesForTeam)) {
                            $fixturesForDate[$team->value] = $_fixturesForTeam;
                        }
                    }
                }
                if (!empty($fixturesForDate)) {
                    $fixtures[$date] = $fixturesForDate;
                }
            }
        }

        $context = [
            'teamsForm' => $teamsForm->createView(),
            'teams' => $teams,
            'fixtures' => $fixtures,
        ];

        // This could be done in an event listener
        if ($request->getAcceptableContentTypes()[0] === 'application/json') {
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
        $id = $fixture->getId();
        if ($this->isCsrfTokenValid('delete' . $fixture->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($fixture);
            $entityManager->flush();
            $this->addFlash('success', 'Fixture ' . $id . ' deleted');
        }

        return $this->redirectToRoute('app_fixture_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/byDate/{date}', name: 'app_fixture_byDate', methods: ['GET'])]
    public function byDate(
        string            $date,
        FixtureRepository $fixtureRepository,
    ): Response
    {
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

    // This will be removed when we get to the new website
    #[Route('forOldWebsite', name: 'app_fixture_for_old_website', methods: ['GET'])]
    public function forOldWebsite(Request $request, SerializerInterface $serializer): Response|JsonResponse
    {
        $team = Team::getBy($request->query->get('team'));
        $fixtures = [];
        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            $_fixtures = $this->fixtureRepository->getFixturesForTeam($team, $date);
            if (!empty($_fixtures)) {
                $fixture = $_fixtures[0];
                $club = $fixture->getClub();
                $fixtures[] = [
                    "id" => $fixture->getId(),
                    "opponent" => $club?->getName() ?: 'Training',
                    "competition" => $this->translateCompetition($fixture->getCompetition()),
                    "venue" => $fixture->getHomeAway() == HomeAway::Home ? 'home' : 'away',
                    "date" => $date,
                ];
            }
        }

        // This could be done in an event listener
        if ($request->getAcceptableContentTypes()[0] === 'application/json') {
            $json = $serializer->serialize($fixtures, 'json');
            return new JsonResponse($json, 200, [], true);
        }

        return new Response("Unsupported accept type", 400);
    }

    private function translateCompetition($competition): string|null
    {
        return match ($competition) {
            Competition::CountyCup, Competition::NationalCup => 'cup',
            Competition::League => 'league',
            default => null,
        };
    }
}
/*
    competition: 'cup',
    competition: 'friendly',
    competition: 'league',
    competition: 'null',
[
  {
      id: '1624',
    relatedMatchReport: null,
    opponent: 'Fakenham (Senior Squad)',
    competition: 'friendly',
    venue: 'home',
    result: '47:5',
    date: '2024-08-17',
    __typename: 'fixtures'
  },
  {
      id: '1535',
    relatedMatchReport: {
      slug: 'norwich-39-north-walsham-raiders-24',
      __typename: 'matchReports'
    },
    opponent: 'North Walsham 2',
    competition: 'league',
    venue: 'home',
    result: '39:24',
    date: '2024-09-21',
    __typename: 'fixtures'
  }
*/
