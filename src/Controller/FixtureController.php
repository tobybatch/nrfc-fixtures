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
