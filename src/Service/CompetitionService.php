<?php

namespace App\Service;

use App\Config\Competition;
use App\Config\Team;
use App\Entity\Club;

class CompetitionService
{

    private TeamService $teamService;

    public function __construct(TeamService $teamService) {
        $this->teamService = $teamService;
    }

    public function parseCompetition(Team $team, ?string $name): Competition
    {
        $_name = strtolower($name);

        if (empty($_name)) {
            return Competition::None;
        }

        // Define patterns that should return Competition::None
        $nonePatterns = [
            'NAW',
            'Christmas',
            'Mothering Sunday',
            'OUT OF SEASON',
            'NAW',
            'Bye'
        ];

        foreach ($nonePatterns as $pattern) {
            if (stripos($_name, $pattern) !== false) {
                return Competition::None;
            }
        }

        // seniors only play a league cup
        if ($this->teamService->isSenior($team)) {
            return Competition::League;
        }

        // Youth and Minis

        if (str_contains($_name, 'norfolk') && str_contains($_name, '10s')) {
            return Competition::Norfolk10s;
        }

        if (str_contains($_name, 'festival')) {
            return Competition::Festival;
        }

        if (str_starts_with($_name, 'nat') && str_contains($_name, 'cup')) {
            return Competition::NationalCup;
        }

        if (str_contains($_name, 'conference')) {
            return Competition::Conference;
        }

        if (str_contains($_name, 'county') && str_contains($_name, 'cup')) {
            return Competition::CountyCup;
        }

        if (str_contains($_name, 'pathway') || str_contains($_name, 'cb') || str_contains($_name, 'academy')) {
            return Competition::Pathway;
        }

        return Competition::Friendly;
    }

}