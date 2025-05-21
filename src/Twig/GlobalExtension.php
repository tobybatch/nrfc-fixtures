<?php

namespace App\Twig;

use App\Entity\Club;
use App\Service\PreferencesService;
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
    private PreferencesService $preferencesService;

    public function __construct(RequestStack $requestStack, PreferencesService $preferencesService)
    {
        $this->requestStack = $requestStack;
        $this->preferencesService = $preferencesService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pageHasHelp', [$this, 'pageHasHelp']),
            new TwigFunction('pageHelpIsVisible', [$this, 'pageHelpIsVisible']),
            new TwigFunction('getPreference', [$this, 'getPreference']),
        ];
    }

    public function getPreference(string $path): bool
    {
        $segments = explode('.', $path);

        // Start with the initial array
        $current = $this->preferencesService->getPreferences();

        // Traverse through each segment
        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }
        return $current;
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

        $preferences = $this->preferencesService->getPreferences();
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
