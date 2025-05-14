<?php

namespace App\Twig;

use App\Entity\Club;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GlobalExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('showHelp', [$this, 'showHelp']),
        ];
    }

    public function showHelp(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return true;
        }

        $routeName = $request->attributes->get('_route');

        return $routeName == 'app_fixture_index';
    }
}
