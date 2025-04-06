<?php

namespace App\Controller;

use App\Config\Team;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/team')]
final class TeamController extends AbstractController
{
    #[Route('/lookup', name: 'app_team_lookup')]
    public function lookupByName(Request $request, LoggerInterface $logger): JsonResponse
    {
        $name = $request->get('name');
        if (empty($name)) {
            return new JsonResponse(
                $this->error("No team name provided"),
                400
            );
        }
        return match ($name) {
            "Mini Section Teams" => new JsonResponse(['name' => Team::Minis]),
            "Under 13s" => new JsonResponse(['name' => Team::U13B]),
            "Under 14s" => new JsonResponse(['name' => Team::U14B]),
            "Under 15s" => new JsonResponse(['name' => Team::U15B]),
            "Under 16s" => new JsonResponse(['name' => Team::U16B]),
            "Colts Under 18s" => new JsonResponse(['name' => Team::U18B]),
            "Under 12s Girls" => new JsonResponse(['name' => Team::U12G]),
            "Under 14s Girls" => new JsonResponse(['name' => Team::U14G]),
            "Under 16s Girls" => new JsonResponse(['name' => Team::U16G]),
            "Under 18s Girls" => new JsonResponse(['name' => Team::U18G]),
            "1st XV" => new JsonResponse(['name' => Team::FIRST_XV]),
            "2nd XV Team (Lions)" => new JsonResponse(['name' => Team::LIONS]),
            "3rd XV (AXV)" => new JsonResponse(['name' => Team::AXV]),
            "Senior Women" => new JsonResponse(['name' => Team::SENIOR_WOMEN]),
            default => (function () use ($name, $logger) {
                $logger->error("Unsupported team name: $name");
                return new JsonResponse(
                    $this->error("Unsupported team name: $name"),
                    404
                );
            })(),
        };
    }

    private function error(string $message): array
    {
        return [
            'state' => 'error',
            'message' => $message,
        ];
    }
}
