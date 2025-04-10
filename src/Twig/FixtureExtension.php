<?php

namespace App\Twig;

use App\Config\Competition;
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
        ];
    }

    public function dateIsNew(DateTimeImmutable $d1, DateTimeImmutable $d2): bool
    {
        return $d1->format('Y-m-d') === $d2->format('Y-m-d');
    }

    public function shortCompetition(Competition $competition): string
    {
        switch ($competition) {
            case Competition::CountyCup:
                return "(CC)";
            case Competition::NationalCup:
                return "(NC)";
            case Competition::Pathway:
            case Competition::Festival:
            case Competition::Norfolk10s:
            case Competition::Conference:
            case Competition::None:
            case Competition::Friendly:
                return "";
        }
    }
}