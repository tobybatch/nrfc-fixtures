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
        return '12:01' != $date->format('H:i');
    }

    public function fixtureSummary(Fixture $fixture): string
    {
        if (Competition::None === $fixture->getCompetition()) {
            return $fixture->getTeam()->value . ' Training';
        } elseif (null != $fixture->getClub()) {
            return sprintf(
                '%s vs %s (%s)',
                $fixture->getTeam()->value,
                $fixture->getClub()->getName(),
                $fixture->getHomeAway()->value,
            );
        } else {
            return $fixture->getTeam()->value.' '.$fixture;
        }
    }
}
