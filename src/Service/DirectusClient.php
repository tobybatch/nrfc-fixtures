<?php

namespace App\Service;

use PHPUnit\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DirectusClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $apiUrl,
        private readonly string $apiToken)
    {

    }

    public function fetchCollection(string $collection, array $params = []): array
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl . '/items/' . $collection, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'query' => $params,
            ]);

            return $response->toArray()['data'] ?? [];
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
        return [];
    }

    public function fetchItem(string $collection, string|int $id): array
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl . '/items/' . $collection . '/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
            ]);

            return $response->toArray()['data'] ?? [];
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return [];
    }
}
