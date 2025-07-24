<?php

namespace App\Service;

use App\Config\Competition;

class CompetitionService
{

    public function parseCompetition(Team $team, Club $club, ?string $name): Competition
    {
        if (empty($name)) {
            return Competition::None;
        }

        // Define patterns that should return Competition::None
        $nonePatterns = [
            'NAW',
            'Christmas',
            'Mothering Sunday',
            'OUT OF SEASON'
        ];

        foreach ($nonePatterns as $pattern) {
            if (stripos($name, $pattern) !== false) {
                return Competition::None;
            }
        }

        // seniors only play a league cup

        return Competition::None;
    }

}