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
    public function getFilters()
    {
        return [
            new TwigFunction('dateIsNew', [$this, 'dateIsNew']),
            new TwigFilter('shortCompetition', [$this, 'shortCompetition']),
            new TwigFilter('fixtureToString', [$this, 'fixtureToString']),
        ];
    }

    public function dateIsNew(DateTimeImmutable $d1, DateTimeImmutable $d2): bool
    {
        return $d1->format('Y-m-d') === $d2->format('Y-m-d');
    }
}