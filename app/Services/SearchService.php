<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Profile;
use App\Models\Track;
use App\Services\CoverArtService;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    private const VALID_TYPES = ['artist', 'album', 'track', 'user'];
    private const MB_CACHE_TTL = 300; // 5 minutes

    public function __construct(private MusicBrainzService $musicBrainz) {}

    /**
     * Search across specified types, merging local and MusicBrainz results.
     */
    public function search(string $query, array $types = [], int $limit = 15): array
    {
        $types = !empty($types)
            ? array_intersect($types, self::VALID_TYPES)
            : self::VALID_TYPES;

        $results = [];

        foreach ($types as $type) {
            $results[$type . 's'] = match ($type) {
                'artist' => $this->searchArtists($query, $limit),
                'album' => $this->searchAlbums($query, $limit),
                'track' => $this->searchTracks($query, $limit),
                'user' => $this->searchUsers($query, $limit),
            };
        }

        return $results;
    }

    private function searchArtists(string $query, int $limit): array
    {
        $local = Artist::search($query)
            ->take($limit)
            ->get()
            ->map(fn (Artist $artist) => [
                'mbid' => $artist->mbid,
                'name' => $artist->name,
                'type' => $artist->type,
                'country' => $artist->country,
                'disambiguation' => $artist->disambiguation,
            ])
            ->keyBy('mbid');

        $mbResults = $this->cachedMbSearch('artist', $query, $limit, function () use ($query, $limit) {
            $mbData = $this->musicBrainz->searchArtists($query, $limit);

            return collect($mbData['artists'] ?? [])
                ->map(fn (array $item) => [
                    'mbid' => $item['id'],
                    'name' => $item['name'],
                    'type' => $item['type'] ?? null,
                    'country' => $item['country'] ?? null,
                    'disambiguation' => $item['disambiguation'] ?? null,
                ])
                ->keyBy('mbid');
        });

        return $mbResults->merge($local)->values()->take($limit)->all();
    }

    private function searchAlbums(string $query, int $limit): array
    {
        $local = Album::search($query)
            ->take($limit)
            ->get()
            ->load('artists')
            ->map(fn (Album $album) => [
                'mbid' => $album->mbid,
                'title' => $album->title,
                'type' => $album->type,
                'release_date' => $album->release_date?->toDateString(),
                'cover_art_url' => $album->cover_art_url,
                'artists' => $album->artists->map(fn (Artist $a) => [
                    'mbid' => $a->mbid,
                    'name' => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ])
            ->keyBy('mbid');

        $mbResults = $this->cachedMbSearch('album', $query, $limit, function () use ($query, $limit) {
            $mbData = $this->musicBrainz->searchAlbums($query, $limit);

            return collect($mbData['release-groups'] ?? [])
                ->filter(fn (array $rg) => in_array($rg['primary-type'] ?? null, ['Album', 'EP'], true)
                    && empty($rg['secondary-types'] ?? []))
                ->map(fn (array $rg) => [
                    'mbid' => $rg['id'],
                    'title' => $rg['title'],
                    'type' => $rg['primary-type'] ?? null,
                    'release_date' => $rg['first-release-date'] ?? null,
                    'cover_art_url' => CoverArtService::url($rg['id']),
                    'artists' => collect($rg['artist-credit'] ?? [])->filter(fn ($credit) => is_array($credit))->map(fn (array $credit) => [
                        'mbid' => $credit['artist']['id'] ?? null,
                        'name' => $credit['artist']['name'] ?? $credit['name'] ?? null,
                        'join_phrase' => $credit['joinphrase'] ?? null,
                    ])->values()->all(),
                ])
                ->keyBy('mbid');
        });

        return $mbResults->merge($local)->values()->take($limit)->all();
    }

    private function searchTracks(string $query, int $limit): array
    {
        $local = Track::search($query)
            ->take($limit)
            ->get()
            ->load(['album', 'artists'])
            ->map(fn (Track $track) => [
                'mbid' => $track->mbid,
                'title' => $track->title,
                'length' => $track->length,
                'album' => $track->album ? [
                    'mbid' => $track->album->mbid,
                    'title' => $track->album->title,
                ] : null,
                'artists' => $track->artists->map(fn (Artist $a) => [
                    'mbid' => $a->mbid,
                    'name' => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ])
            ->keyBy('mbid');

        $mbResults = $this->cachedMbSearch('track', $query, $limit, function () use ($query, $limit) {
            $mbData = $this->musicBrainz->searchTracks($query, $limit);

            return collect($mbData['recordings'] ?? [])
                ->map(fn (array $rec) => [
                    'mbid' => $rec['id'],
                    'title' => $rec['title'],
                    'length' => $rec['length'] ?? null,
                    'album' => isset($rec['releases'][0]) ? [
                        'mbid' => $rec['releases'][0]['id'],
                        'title' => $rec['releases'][0]['title'],
                    ] : null,
                    'artists' => collect($rec['artist-credit'] ?? [])->filter(fn ($credit) => is_array($credit))->map(fn (array $credit) => [
                        'mbid' => $credit['artist']['id'] ?? null,
                        'name' => $credit['artist']['name'] ?? $credit['name'] ?? null,
                        'join_phrase' => $credit['joinphrase'] ?? null,
                    ])->values()->all(),
                ])
                ->keyBy('mbid');
        });

        return $mbResults->merge($local)->values()->take($limit)->all();
    }

    private function searchUsers(string $query, int $limit): array
    {
        return Profile::search($query)
            ->take($limit)
            ->get()
            ->map(fn (Profile $profile) => [
                'id' => $profile->user_id,
                'username' => $profile->username,
                'display_name' => $profile->display_name,
                'avatar' => $profile->avatar,
            ])
            ->all();
    }

    private function cachedMbSearch(string $type, string $query, int $limit, callable $fetcher): \Illuminate\Support\Collection
    {
        $key = "mb_search:{$type}:" . md5("{$query}:{$limit}");

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $results = $fetcher();

        if ($results->isNotEmpty()) {
            Cache::put($key, $results, self::MB_CACHE_TTL);
        }

        return $results;
    }
}
