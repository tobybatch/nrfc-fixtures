<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PreferencesService
{
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getCurrentRequest()->getSession();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreferences(): array
    {
        $preferences = $this->getSession()->get('preferences', []) ?? [];
        $this->logger->debug('Getting preferences', $preferences);

        return $preferences;
    }

    public function setPreferences(string $key, mixed $value): void
    {
        $targetArray = $this->getPreferences();
        $targetArray[$key] = $value;
        $this->logger->debug('Setting preferences ' . $key, $value);
        $this->getSession()->set('preferences', $targetArray);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->getPreferences();
    }
}
