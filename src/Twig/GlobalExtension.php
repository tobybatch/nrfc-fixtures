<?php

namespace App\Twig;

use App\Entity\Club;
use Psr\Log\LoggerInterface;
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
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pageHasHelp', [$this, 'pageHasHelp']),
            new TwigFunction('pageHelpIsVisible', [$this, 'pageHelpIsVisible']),
        ];
    }

    public function pageHasHelp(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return true;
        }

        $routeName = $request->attributes->get('_route');

        return $routeName == 'app_fixture_index';
    }

    public function pageHelpIsVisible(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return true;
        }

        $user = $request->getSession()->get('user');
        $userPreferences = [];
        if ($user) {
            $userPreferences = $user->getPreferences();
        }
        $preferences = array_merge(
            $request->getSession()->get('preferences', []),
            $userPreferences
        );

        $routeName = $request->attributes->get('_route');

        if (
            array_key_exists('showHelp', $preferences) &&
            array_key_exists($routeName, $preferences['showHelp'])
        ) {
            return $preferences['showHelp'][$routeName];
        }
        return true;
    }
}
