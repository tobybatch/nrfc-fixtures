<?php

namespace App\Controller;

use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/health')]
class HealthController extends AbstractController
{
    #[Route('/', name: 'health_index')]
    public function index(): JsonResponse
    {
        return new JsonResponse(
            json_encode([
                'status' => 'all good'
            ]),
            200,
            [],
            true
        );
    }
}