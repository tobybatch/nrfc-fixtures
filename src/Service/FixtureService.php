<?php

namespace App\Service;

use App\Config\Competition;
use App\Entity\Fixture;

class FixtureService
{
    private TeamService $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    public function format(
        Fixture $fixture,
        bool    $incHA = true,
        bool    $incComp = false,
    ): string
    {
        if (null != $fixture->getClub()) {
            $text = $fixture->getClub()->getName();
            if ($this->teamService->isSenior($fixture->getTeam())) {
                $text .= ' ' . substr($fixture->getTeam()->value, 0, 1);
            } else {
                $text .= ' ' . $fixture->getTeam()->value;
            }
        } elseif (!empty($fixture->getName())) {
            $text = $fixture->getName();
        } elseif (Competition::None != $fixture->getCompetition()) {
            $text = $fixture->getCompetition()->value;
        } else {
            $text = 'Training?';
        }

        if ($text != 'Training?') {
            if ($incHA) {
                $text .= ' (' . $fixture->getHomeAway()->value . ')';
            }

            if ($incComp && $fixture->getCompetition() !== Competition::None) {
                $text .= ' [' . $fixture->getCompetition()->shortValue() . ']';
            }
        }

        return $text;
    }

    public function fullName(Fixture $fixture): string
    {
        return $this->format($fixture);
    }
}
