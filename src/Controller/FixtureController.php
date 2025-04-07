<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FixtureController extends AbstractController
{
    #[Route('/fixture', name: 'app_fixture')]
    public function index(): Response
    {
        return $this->render('fixture/index.html.twig', [
            'controller_name' => 'FixtureController',
        ]);
    }
}
