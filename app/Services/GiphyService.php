<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GiphyService
{
    private PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::baseUrl('https://api.giphy.com/v1')
            ->timeout(8)
            ->withQueryParameters(['api_key' => config('services.giphy.api_key')]);
    }

    /**
     * Trending GIFs — cached for 10 minutes.
     */
    public function trending(int $limit = 24, int $offset = 0, string $rating = 'pg-13'): array
    {
        $cacheKey = "giphy.trending.{$limit}.{$offset}.{$rating}";

        return Cache::remember($cacheKey, 600, function () use ($limit, $offset, $rating) {
            $response = $this->client->get('/gifs/trending', [
                'limit'  => $limit,
                'offset' => $offset,
                'rating' => $rating,
                'bundle' => 'messaging_non_clips',
            ]);

            return $this->formatResponse($response->json());
        });
    }

    /**
     * Search GIFs — cached for 5 minutes per query.
     */
    public function search(string $query, int $limit = 24, int $offset = 0, string $rating = 'pg-13'): array
    {
        $cacheKey = 'giphy.search.' . md5("{$query}.{$limit}.{$offset}.{$rating}");

        return Cache::remember($cacheKey, 300, function () use ($query, $limit, $offset, $rating) {
            $response = $this->client->get('/gifs/search', [
                'q'      => $query,
                'limit'  => $limit,
                'offset' => $offset,
                'rating' => $rating,
                'bundle' => 'messaging_non_clips',
            ]);

            return $this->formatResponse($response->json());
        });
    }

    /**
     * Normalise the Giphy response to only what the frontend needs.
     */
    private function formatResponse(array $response): array
    {
        $gifs = collect($response['data'] ?? [])->map(fn (array $gif) => [
            'id'          => $gif['id'],
            'title'       => $gif['title'] ?? '',
            'url'         => $gif['images']['fixed_height']['url'] ?? $gif['images']['original']['url'],
            'preview_url' => $gif['images']['fixed_height_small']['url'] ?? $gif['images']['fixed_height']['url'],
            'width'       => (int) ($gif['images']['fixed_height']['width'] ?? 0),
            'height'      => (int) ($gif['images']['fixed_height']['height'] ?? 0),
        ]);

        return [
            'data'       => $gifs,
            'pagination' => [
                'total'  => $response['pagination']['total_count'] ?? 0,
                'count'  => $response['pagination']['count'] ?? 0,
                'offset' => $response['pagination']['offset'] ?? 0,
            ],
        ];
    }
}
