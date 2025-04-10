<?php

namespace App\Controller;

use App\Config\Team;
use App\Repository\FixtureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FixtureController extends AbstractController
{
    private FixtureRepository $fixtureRepository;

    public function __construct(FixtureRepository $fixtureRepository)
    {
        $this->fixtureRepository = $fixtureRepository;
    }

    #[Route('/fixture/all', name: 'app_fixture_all')]
    public function all(): Response
    {
        $fixtures = [];

        $dates = $this->fixtureRepository->getDates();
        foreach ($dates as $date) {
            $fixtures[$date] = [
                Team::Minis->value => $this->fixtureRepository->getFixturesForTeam(Team::Minis, $date),
                Team::U13B->value => $this->fixtureRepository->getFixturesForTeam(Team::U13B, $date),
                Team::U14B->value => $this->fixtureRepository->getFixturesForTeam(Team::U14B, $date),
                Team::U15B->value => $this->fixtureRepository->getFixturesForTeam(Team::U15B, $date),
                Team::U16B->value => $this->fixtureRepository->getFixturesForTeam(Team::U16B, $date),
                Team::U18B->value => $this->fixtureRepository->getFixturesForTeam(Team::U18B, $date),
                Team::U12G->value => $this->fixtureRepository->getFixturesForTeam(Team::U12G, $date),
                Team::U14G->value => $this->fixtureRepository->getFixturesForTeam(Team::U14G, $date),
                Team::U16G->value => $this->fixtureRepository->getFixturesForTeam(Team::U16G, $date),
                Team::U18G->value => $this->fixtureRepository->getFixturesForTeam(Team::U18G, $date),
            ];
        }

        return $this->render('fixture/all.html.twig', [
            'fixtures' => $fixtures,
        ]);
    }

    #[Route('/fixture/team/{team}', name: 'app_fixture_team')]
    public function team(Team $team): Response
    {
        $fixtures = $this->fixtureRepository->getFixturesForTeam($team);
        return $this->render('fixture/team.html.twig', [
            'fixtures' => $fixtures,
        ]);
    }
}
