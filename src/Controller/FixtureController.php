<?php

namespace App\Controller;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Fixture;
use App\Form\FixturesDisplayOptionsForm;
use App\Form\FixtureType;
use App\Form\Model\FixturesDisplayOptionsDTO;
use App\Repository\FixtureRepository;
use App\Service\FixtureService;
use App\Service\PreferencesService;
use App\Service\TeamService;
use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FixtureRepository $fixtureRepository,
        private readonly PreferencesService $preferencesService,
        private readonly TeamService $teamService,
        private readonly LoggerInterface $logger,
        private readonly FixtureService $fixtureService,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route(name: 'app_fixture_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response|JsonResponse
    {
        $isJson = 'application/json' === $request->getAcceptableContentTypes()[0];

        if ($request->isMethod('GET')) {
            $team = $request->query->get('team');
            $this->logger->debug('Team param', ['team' => $team]);
            if (null !== $team) {
                $selectedTeams = match ($team) {
                    'boys' => $this->teamService->getBoys(),
                    'girls' => $this->teamService->getGirls(),
                    'youth' => $this->teamService->getYouth(),
                    'seniors' => $this->teamService->getSeniors(),
                    default => [$this->teamService->getBy($team)],
                };
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
            $teams = $this->teamService->getYouth();
        } else {
            $teams = array_map(fn ($team) => $this->teamService->getBy($team), $_teams);
        }

        $this->logger->debug('Teams', ['teams' => $teams]);

        $showPastDates = $this->preferencesService->getPreferences()['showPastDates'] ?? $isJson;
        $today = new \DateTimeImmutable('today');

        $fixtures = [];
        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            // check date is today or later, or force show is set
            $eventDateNormalized = $date->setTime(0, 0, 0);
            if ($showPastDates || $eventDateNormalized >= $today) {
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
                    $fixtures[$date->format('Y-m-d')] = $fixturesForDate;
                }
            }
        }

        $context = [
            'teamsForm' => $teamsForm->createView(),
            'teams' => $teams,
            'fixtures' => $fixtures,
        ];

        // This could be done in an event listener
        if ($isJson) {
            $json = $this->serializer->serialize(
                $context,
                'json', [
                'groups' => ['fixture:read']
            ]);
            $this->logger->info('Context', ['json' => $json]);

            return new JsonResponse($json, 200, [], true);
        }

        return $this->render('fixture/index.html.twig', $context);
    }

    #[Route('/new', name: 'app_fixture_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $fixture = new Fixture();

        $team = $request->query->get('team');
        $date = $request->query->get('date');

        if ($team) {
            $_team = $this->teamService->getBy($team);
            $fixture->setTeam($_team);
        }

        if ($date) {
            // Parse the date string if needed (adjust format as necessary)
            try {
                $dateObject = \DateTimeImmutable::createFromFormat('d-m-Y H:i', $date);
                if ($dateObject) {
                    $fixture->setDate($dateObject);
                }
            } catch (\Exception $e) {
                $this->logger->error('Can\'t parse date', ['date' => $date, 'exception' => $e]);
            }
        }

        $this->logger->info('fixture', ['fixture' => $fixture]);
        $form = $this->createForm(FixtureType::class, $fixture, [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($fixture);
            $this->entityManager->flush();

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
    public function edit(Request $request, Fixture $fixture): Response
    {
        $form = $this->createForm(FixtureType::class, $fixture,  [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_fixture_show', ['id' => $fixture->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('fixture/edit.html.twig', [
            'fixture' => $fixture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fixture_delete', methods: ['POST'])]
    public function delete(Request $request, Fixture $fixture): Response
    {
        $id = $fixture->getId();
        if ($this->isCsrfTokenValid('delete'.$fixture->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($fixture);
            $this->entityManager->flush();
            $this->addFlash('success', 'Fixture '.$id.' deleted');
        }

        return $this->redirectToRoute('app_fixture_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/byDate/{date}', name: 'app_fixture_byDate', methods: ['GET'])]
    public function byDate(
        string $date,
        FixtureRepository $fixtureRepository,
    ): Response {
        // Manually validate the date format
        $dateObj = \DateTimeImmutable::createFromFormat('Ymd', $date);

        if (!$dateObj || $dateObj->format('Ymd') !== $date) {
            return $this->json([
                'error' => 'Invalid date format. Please use Ymd format (e.g., 20230501 for May 1, 2023).',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Create DateTimeImmutable from the input string
            $dateObj = \DateTimeImmutable::createFromFormat('Ymd', $date);

            if (!$dateObj) {
                throw new \InvalidArgumentException('Invalid date');
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
        } catch (\Exception $e) {
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

    /**
     * @throws \DateMalformedStringException
     */
    #[Route('spond/{team}', name: 'app_fixture_spond', defaults: ['team' => null], methods: ['GET'])]
    public function spond(?Team $team = null): Response|JsonResponse
    {
        if (!$team) {
            return $this->render('fixture/spond.html.twig');
        }

        $fixtures = $this->fixtureRepository->findByTeam($team);
        $handle = fopen('php://memory', 'r+');
        /**
         * @var Fixture $fixture
         */
        foreach ($fixtures as $fixture) {
            if (!in_array(
                $fixture->getCompetition(),
                [Competition::None, Competition::Training, Competition::Pathway],
            )) {
                fputcsv($handle, [
                    $fixture->getDate()->format('d/m/Y'),
                    '00:00:00' === $fixture->getDate()->format('H:i:s') ? '11:00' : $fixture->getDate()->format('H:i:s'),
                    '01:00',
                    $fixture->getDate()->format('d/m/Y'),
                    '00:00:00' === $fixture->getDate()->format('H:i:s') ? '13:00' : $fixture->getDate()->modify('+2 hours')->format('H:i:s'),
                    HomeAway::Home == $fixture->getHomeAway() ? 'Home match' : 'Away match',
                    HomeAway::Home == $fixture->getHomeAway() ? 'Norwich '.$team->value : $fixture->getClub()?->getName().$team->value,
                    HomeAway::Home == $fixture->getHomeAway() ? $fixture->getClub()?->getName().' '.$team->value : 'Norwich '.$team->value,
                    $this->fixtureService->format($fixture),
                    $fixture->getClub()?->getAddress(),
                ]);
            }
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Create response with appropriate headers
        return new Response(
            $csv,
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => sprintf('attachment; filename="%s-for-spond.csv"', $team->name),
                'Cache-Control' => 'no-store, no-cache',
            ]
        );
    }

    // This will be removed when we get to the new website
    #[Route('forOldWebsite', name: 'app_fixture_for_old_website', methods: ['GET'])]
    public function forOldWebsite(Request $request): Response|JsonResponse
    {
        $team = $this->teamService->getBy($request->query->get('team'));
        $fixtures = [];
        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            $_fixtures = $this->fixtureRepository->getFixturesForTeam($team, $date);
            if (!empty($_fixtures)) {
                $fixture = $_fixtures[0];
                $fixtures[] = [
                    'id' => $fixture->getId(),
                    'opponent' => $this->fixtureService->format($fixture, false),
                    'competition' => $this->translateCompetition($fixture->getCompetition()),
                    'venue' => HomeAway::Home == $fixture->getHomeAway() ? 'home' : 'away',
                    'date' => $date,
                    'slug' => $fixture->getMatchReportExternalId(),
                ];
            }
        }

        // This could be done in an event listener
        if ('application/json' === $request->getAcceptableContentTypes()[0]) {
            $json = $this->serializer->serialize($fixtures, 'json');

            return new JsonResponse($json, 200, [], true);
        }

        return new Response('Unsupported accept type', 400);
    }

    private function translateCompetition(Competition $competition): ?string
    {
        return match ($competition) {
            Competition::CountyCup, Competition::NationalCup => 'cup',
            Competition::League => 'league',
            default => null,
        };
    }
}