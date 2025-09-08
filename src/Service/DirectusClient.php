<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DirectusClient
{
    private string $apiUrl;
    private string $apiToken;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client, string $apiUrl, string $apiToken)
    {
        $this->client = $client;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiToken = $apiToken;
    }

    public function fetchCollection(string $collection, array $params = []): array
    {
        $response = $this->client->request('GET', $this->apiUrl . '/items/' . $collection, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            'query' => $params,
        ]);

        return $response->toArray()['data'] ?? [];
    }

    public function fetchItem(string $collection, string|int $id): array
    {
        $response = $this->client->request('GET', $this->apiUrl . '/items/' . $collection . '/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
        ]);

        return $response->toArray()['data'] ?? [];
    }
}
