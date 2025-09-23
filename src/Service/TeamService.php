<?php

namespace App\Service;

use App\Config\Team;

class TeamService
{
    /**
     * @return array<Team>
     */
    public function getBoys(): array
    {
        return [
            Team::Minis,
            Team::U13B,
            Team::U14B,
            Team::U15B,
            Team::U16B,
            Team::U18B,
        ];
    }

    /**
     * @return array<Team>
     */
    public function getGirls(): array
    {
        return [
            Team::Minis,
            Team::U12G,
            Team::U14G,
            Team::U16G,
            Team::U18G,
        ];
    }

    /**
     * @return array<Team>
     */
    public function getSeniors(): array
    {
        return [
            Team::FIRST_XV_MEN,
            Team::SECOND_XV_MEN,
            Team::FIRST_XV_WOMEN,
        ];
    }

    /**
     * @return array<Team>
     */
    public function getYouth(): array
    {
        return [
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
        ];
    }

    public function getBy(string $value): ?Team
    {
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
            '1st Team (Mens)', 'FIRST_XV_MEN', '1st XV Fixture' => Team::FIRST_XV_MEN,
            '2nd Team (Mens)', 'SECOND_XV_MEN', 'Lions Fixture', '2nd XV (Lions)' => Team::SECOND_XV_MEN,
            '1st Team (Women)', 'FIRST_XV_WOMEN' => Team::FIRST_XV_WOMEN,
            default => null,
        };
    }

    public function isSenior(?Team $team): bool
    {
        return in_array($team, $this->getSeniors());
    }

    public function findOpponent(string $clubAndTeam, Team $team): ?Team
    {
        $words = explode(' ', $clubAndTeam);
        if (count($words) < 2) {
            return null;
        }
        $lastWord = $words[count($words) - 1];
        return match ($lastWord) {
            '1', 'I' => ($team === Team::FIRST_XV_WOMEN) ? Team::FIRST_XV_WOMEN : Team::FIRST_XV_MEN,
            '2', 'II' => Team::SECOND_XV_MEN,
            '3', 'III' => Team::THIRD_XV_MEN,
            '4', 'IV' => Team::FOURTH_XV_MEN,
            default => null,
        };
    }
}
