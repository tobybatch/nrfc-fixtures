<?php

namespace App\Twig;

use App\Entity\Club;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MapExtension extends AbstractExtension
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig; // Inject Twig's Environment
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('makeMap', [$this, 'makeMap'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function makeMap(?Club $club): string
    {
        if (!$club) {
            return '';
        }

        $myMap = false;
        if ($club->getLatitude() && $club->getLongitude()) {
            $point = new Point($club->getLatitude(), $club->getLongitude());
            $myMap = (new Map())->center($point)
                ->zoom(10)
                ->addMarker(new Marker(
                    position: $point,
                    title: $club->getName(),
                ));
        }

        return $this->twig->render(
            'components/map.html.twig',
            [
                'address' => $club->getAddress(),
                'map' => $myMap,
            ]);
    }
}
