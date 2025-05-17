<?php

namespace App\Twig;

use App\Config\Competition;
use App\Entity\Fixture;
use DateTimeImmutable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FixtureExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            //            new TwigFilter('teamName', [$this, 'teamName']),
            new TwigFilter('fixtureSummary', [$this, 'fixtureSummary']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dateIsNew', [$this, 'dateIsNew']),
            new TwigFunction('dateIsNotSet', [$this, 'dateIsNotSet']),
        ];
    }

    public function dateIsNew(DateTimeImmutable $d1, DateTimeImmutable $d2): bool
    {
        return $d1->format('Y-m-d') === $d2->format('Y-m-d');
    }

    /**
     * Default tim eof a date is set to 1 minute past midnight, it the time is still
     * 12:01 then it has not been set, return true. If it's been changed then assume
     * that is deliberate and return false.
     */
    public function dateIsNotSet(DateTimeImmutable $date): bool
    {
        $d = $date->format('H:i');
        return '00:00' == $date->format('H:i');
    }

    public function fixtureSummary(Fixture $fixture): string
    {
        $club = $fixture->getClub();
        $comp = $fixture->getCompetition();

        if ($comp == Competition::Training && $club) {
            // If there is a club then it may be cluster training
            return sprintf(
                "%s Training with %s (%s)",
                $fixture->getTeam()->value,
                $club->getName(),
                $fixture->getHomeAway()->value,
            );
        }
        elseif ($comp == Competition::Training && !$club) {
            return sprintf("%s Training", $fixture->getTeam()->value);
        }
        elseif ($club && $comp != Competition::None) {
            // Club Comp   U13 vs Club COMP [HA]
            return sprintf("%s %s vs %s (%s)",
                $fixture->getTeam()->value,
                $club->getName(),
                $comp->value,
                $fixture->getHomeAway()->value,
            );
        }
        elseif ($club && $comp == Competition::None) {
            // Club !Comp  U13 vs Club [HA]
            return sprintf("%s vs %s (%s)",
                $fixture->getTeam()->value,
                $club->getName(),
                $fixture->getHomeAway()->value,

            );
        }
        elseif (!$club && $comp != Competition::None) {
            // !Club Comp  U13 Comp [HA]
            return sprintf("%s %s %s (%s)",
                $fixture->getTeam()->value,
                $fixture->getName(),
                $comp->value,
                $fixture->getHomeAway()->value,
            );
        }
        else {
            // if (!$club && $comp == Competition::None)
            return sprintf("%s %s (%s)",
                $fixture->getTeam()->value,
                $fixture->getName(),
                $fixture->getHomeAway()->value,
            );
        }
    }
}
