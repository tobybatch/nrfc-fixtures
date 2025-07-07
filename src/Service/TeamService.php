<?php

namespace App\Service;

use App\Config\Team;

class TeamService
{
    public function getBoys(): array {
        return array(
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            TEAM::U18B,
        );
    }

    public function getGirls(): array {
        return array(
            Team::Minis,
            Team::U12G,
            Team::U14G,
            Team::U16G,
            Team::U18G,
        );
    }

    public function getSeniors(): array {
        return array(
            Team::FIRST_XV,
            Team::LIONS,
            Team::SENIOR_WOMEN,
        );
    }

    public function getYouth(): array {
        return array(
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            Team::U18B,
            Team::U12G,
            Team::U14G,
            Team::U16G,
            Team::U18G,
        );

    }

    public function getBy(string $value): Team|null {
        return match ($value) {
            'Minis' => Team::Minis,
            'U13B' => Team::U13B,
            'U14B' => Team::U14B,
            'U15B' => Team::U15B,
            'U16B' => Team::U16B,
            'U18B' => Team::U18B,
            'U12G' => Team::U12G,
            'U14G' => Team::U14G,
            'U16G' => Team::U16G,
            'U18G' => Team::U18G,
            '1st Team (Mens)', 'FIRST_XV', '1st XV Fixture' => Team::FIRST_XV,
            '2nd Team (Mens)', 'LIONS', 'Lions Fixture' => Team::LIONS,
            '1st Team (Women)', 'SENIOR_WOMEN' => Team::SENIOR_WOMEN,
            default => null,
        };
    }
}