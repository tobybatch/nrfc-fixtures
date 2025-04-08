<?php

namespace App\Twig;

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
            new TwigFilter('price', [$this, 'formatPrice']),
        ];
    }

    public function dateIsNew(DateTimeImmutable $d1, DateTimeImmutable $d2): bool
    {
        return $d1->format('Y-m-d') === $d2->format('Y-m-d');
    }

    public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
    {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$'.$price;

        return $price;
    }
}