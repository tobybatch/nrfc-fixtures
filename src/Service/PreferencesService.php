<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PreferencesService
{
    private ?SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        $currentRequest = $requestStack->getCurrentRequest();

        if ($currentRequest && $currentRequest->hasSession()) {
            $this->session = $currentRequest->getSession();
        } else {
            // Provide a fallback "null session" to avoid exceptions
            $this->session = null;
        }
    }

    public function getPreferences(): array
    {
        // Return empty preferences if no session is available
        return $this->session?->get('preferences', []) ?? [];
    }

    public function setPreferences(string $path, string|bool $value): array
    {
        if (!$this->session) {
            throw new \RuntimeException('Cannot set preferences without an active session.');
        }

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
        $this->session->set('preferences', $targetArray);
        return $targetArray;
    }
}