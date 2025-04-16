<?php

namespace App\Controller;

use App\Config\Team;
use App\Entity\Fixture;
use App\Form\FixtureType;
use App\Repository\FixtureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fixture')]
final class FixtureController extends AbstractController
{
    private FixtureRepository $fixtureRepository;

    public function __construct(FixtureRepository $fixtureRepository)
    {
        $this->fixtureRepository = $fixtureRepository;
    }

    #[Route(name: 'app_fixture_index', methods: ['GET'])]
    public function index(FixtureRepository $fixtureRepository): Response
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
        return $this->render('fixture/index.html.twig', [
            'fixtures' => $fixtures,
        ]);
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

    #[Route('/{id}', name: 'app_fixture_show', methods: ['GET'])]
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
}
