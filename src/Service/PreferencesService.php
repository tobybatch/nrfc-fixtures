<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
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

    public function setPreferences(string $path, mixed $value): void
    {
        $targetArray = $this->getPreferences();
        $keys = explode('.', $path);
        $current = &$targetArray;

        foreach ($keys as $key) {
            // If the key doesn't exist or isn't an array, initialize it
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key]; // Move deeper
        }

        $current = $value; // Set the final value
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