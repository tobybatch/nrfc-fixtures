<?php

namespace App\Twig;

use App\Config\Competition;
use App\Config\Team;
use App\Entity\Fixture;
use DateTimeImmutable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class FixtureExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('teamName', [$this, 'teamName']),
            new TwigFunction('dateIsNew', [$this, 'dateIsNew']),
            new TwigFilter('shortCompetition', [$this, 'shortCompetition']),
            new TwigFilter('fixtureToString', [$this, 'fixtureToString']),
        ];
    }

    public function teamName(Team $team): string
    {
        return Team::toString($team);
    }

    public function dateIsNew(DateTimeImmutable $d1, DateTimeImmutable $d2): bool
    {
        return $d1->format('Y-m-d') === $d2->format('Y-m-d');
    }
}