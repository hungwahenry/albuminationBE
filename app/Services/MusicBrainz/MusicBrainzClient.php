<?php

namespace App\Services\MusicBrainz;

use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MusicBrainzClient
{
    private string $baseUrl;
    private string $userAgent;

    public function __construct()
    {
        $this->baseUrl = config('services.musicbrainz.base_url');
        $this->userAgent = config('services.musicbrainz.user_agent');
    }

    /**
     * Lookup a specific entity by its MBID.
     */
    public function lookup(string $entity, string $mbid, array $inc = []): ?array
    {
        $params = ['fmt' => 'json'];

        if (!empty($inc)) {
            $params['inc'] = implode('+', $inc);
        }

        return $this->get("/{$entity}/{$mbid}", $params);
    }

    /**
     * Browse entities linked to another entity.
     */
    public function browse(string $entity, string $linkedEntity, string $mbid, int $limit = 100, int $offset = 0, array $inc = [], array $filters = []): ?array
    {
        $params = [
            'fmt' => 'json',
            $linkedEntity => $mbid,
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ];

        if (!empty($inc)) {
            $params['inc'] = implode('+', $inc);
        }

        return $this->get("/{$entity}", $params);
    }

    /**
     * Search for entities matching a query.
     */
    public function search(string $entity, string $query, int $limit = 25, int $offset = 0): ?array
    {
        return $this->get("/{$entity}", [
            'fmt' => 'json',
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'dismax' => 'true',
        ]);
    }

    private function get(string $path, array $params = []): ?array
    {
        try {
            $response = $this->http()->get($this->baseUrl . $path, $params);
        } catch (ConnectionException $e) {
            Log::warning('MusicBrainz API connection failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('MusicBrainz API request failed', [
                'path' => $path,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    private function http(): PendingRequest
    {
        return Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json',
        ])->withOptions([
            'handler' => HandlerStack::create(new StreamHandler()),
        ])->timeout(10)->retry(2, 500);
    }
}
