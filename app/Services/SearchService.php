<?php

namespace App\Services;

use App\Jobs\RefreshMbCacheJob;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Profile;
use App\Models\Track;
use App\Services\CoverArtService;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    private const VALID_TYPES   = ['album', 'user', 'artist', 'track'];
    private const CACHE_TTL     = 86400; // 24 hours
    private const FRESHNESS_TTL = 72000; // 20 hours — triggers background refresh in final 4h window

    public function __construct(private MusicBrainzService $musicBrainz) {}

    public function search(string $query, array $types = [], int $limit = 15): array
    {
        $types = !empty($types)
            ? array_intersect($types, self::VALID_TYPES)
            : self::VALID_TYPES;

        $results = [];
        foreach ($types as $type) {
            $results[$type . 's'] = match ($type) {
                'artist' => $this->searchArtists($query, $limit),
                'album'  => $this->searchAlbums($query, $limit),
                'track'  => $this->searchTracks($query, $limit),
                'user'   => $this->searchUsers($query, $limit),
            };
        }

        return $results;
    }

    /**
     * Called by RefreshMbCacheJob to warm or renew the MB cache for a given type.
     */
    public function warmMbCache(string $type, string $query, int $limit): void
    {
        $results = match ($type) {
            'artist' => $this->fetchMbArtists($query, $limit),
            'album'  => $this->fetchMbAlbums($query, $limit),
            'track'  => $this->fetchMbTracks($query, $limit),
            default  => collect(),
        };

        if ($results->isNotEmpty()) {
            $key = $this->cacheKey($type, $query, $limit);
            Cache::put($key, $results, self::CACHE_TTL);
            Cache::put($key . ':fresh', true, self::FRESHNESS_TTL);
        }
    }

    private function searchArtists(string $query, int $limit): array
    {
        $local = Artist::search($query)
            ->take($limit)
            ->get()
            ->map(fn (Artist $artist) => [
                'mbid'           => $artist->mbid,
                'slug'           => $artist->slug,
                'name'           => $artist->name,
                'type'           => $artist->type,
                'country'        => $artist->country,
                'disambiguation' => $artist->disambiguation,
            ])
            ->keyBy('mbid');

        $mb = $this->cachedMbSearch('artist', $query, $limit);

        return $mb->merge($local)->values()->take($limit)->all();
    }

    private function searchAlbums(string $query, int $limit): array
    {
        $local = Album::search($query)
            ->take($limit)
            ->get()
            ->load('artists')
            ->map(fn (Album $album) => [
                'mbid'          => $album->mbid,
                'title'         => $album->title,
                'type'          => $album->type,
                'release_date'  => $album->release_date?->toDateString(),
                'cover_art_url' => $album->cover_art_url,
                'artists'       => $album->artists->map(fn (Artist $a) => [
                    'mbid'        => $a->mbid,
                    'slug'        => $a->slug,
                    'name'        => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ])
            ->keyBy('mbid');

        $mb = $this->cachedMbSearch('album', $query, $limit);

        return $mb->merge($local)->values()->take($limit)->all();
    }

    private function searchTracks(string $query, int $limit): array
    {
        $local = Track::search($query)
            ->take($limit)
            ->get()
            ->load(['album', 'artists'])
            ->map(fn (Track $track) => [
                'mbid'    => $track->mbid,
                'title'   => $track->title,
                'length'  => $track->length,
                'album'   => $track->album ? [
                    'mbid'          => $track->album->mbid,
                    'title'         => $track->album->title,
                    'cover_art_url' => $track->album->cover_art_url,
                ] : null,
                'artists' => $track->artists->map(fn (Artist $a) => [
                    'mbid'        => $a->mbid,
                    'slug'        => $a->slug,
                    'name'        => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ])
            ->keyBy('mbid');

        $mb = $this->cachedMbSearch('track', $query, $limit);

        return $mb->merge($local)->values()->take($limit)->all();
    }

    private function searchUsers(string $query, int $limit): array
    {
        return Profile::search($query)
            ->take($limit)
            ->get()
            ->map(fn (Profile $profile) => [
                'id'           => $profile->user_id,
                'username'     => $profile->username,
                'display_name' => $profile->display_name,
                'avatar'       => $profile->avatar,
            ])
            ->all();
    }

    /**
     * Return cached MB results immediately. On a miss, block and fetch synchronously
     */
    private function cachedMbSearch(string $type, string $query, int $limit): Collection
    {
        $key = $this->cacheKey($type, $query, $limit);

        if ($cached = Cache::get($key)) {
            if (!Cache::has($key . ':fresh')) {
                RefreshMbCacheJob::dispatch($type, $query, $limit);
            }
            return $cached;
        }

        // First ever search for this query — fetch synchronously so the user gets full results.
        $results = match ($type) {
            'artist' => $this->fetchMbArtists($query, $limit),
            'album'  => $this->fetchMbAlbums($query, $limit),
            'track'  => $this->fetchMbTracks($query, $limit),
            default  => collect(),
        };

        if ($results->isNotEmpty()) {
            Cache::put($key, $results, self::CACHE_TTL);
            Cache::put($key . ':fresh', true, self::FRESHNESS_TTL);
        }

        return $results;
    }

    private function fetchMbArtists(string $query, int $limit): Collection
    {
        $data = $this->musicBrainz->searchArtists($query, $limit);

        return collect($data['artists'] ?? [])
            ->map(fn (array $item) => [
                'mbid'           => $item['id'],
                'name'           => $item['name'],
                'type'           => $item['type'] ?? null,
                'country'        => $item['country'] ?? null,
                'disambiguation' => $item['disambiguation'] ?? null,
            ])
            ->keyBy('mbid');
    }

    private function fetchMbAlbums(string $query, int $limit): Collection
    {
        $data = $this->musicBrainz->searchAlbums($query, $limit);

        return collect($data['release-groups'] ?? [])
            ->filter(fn (array $rg) =>
                in_array($rg['primary-type'] ?? null, ['Album', 'EP'], true) &&
                empty($rg['secondary-types'] ?? [])
            )
            ->map(fn (array $rg) => [
                'mbid'          => $rg['id'],
                'title'         => $rg['title'],
                'type'          => $rg['primary-type'] ?? null,
                'release_date'  => $rg['first-release-date'] ?? null,
                'cover_art_url' => CoverArtService::url($rg['id']),
                'artists'       => collect($rg['artist-credit'] ?? [])
                    ->filter(fn ($credit) => is_array($credit))
                    ->map(fn (array $credit) => [
                        'mbid'        => $credit['artist']['id'] ?? null,
                        'name'        => $credit['artist']['name'] ?? $credit['name'] ?? null,
                        'join_phrase' => $credit['joinphrase'] ?? null,
                    ])->values()->all(),
            ])
            ->keyBy('mbid');
    }

    private function fetchMbTracks(string $query, int $limit): Collection
    {
        $data = $this->musicBrainz->searchTracks($query, $limit);

        return collect($data['recordings'] ?? [])
            ->map(function (array $rec) {
                $release = $rec['releases'][0] ?? null;
                $rgId    = $release['release-group']['id'] ?? null;

                return [
                    'mbid'    => $rec['id'],
                    'title'   => $rec['title'],
                    'length'  => $rec['length'] ?? null,
                    'album'   => $release ? [
                        'mbid'          => $rgId ?? $release['id'],
                        'title'         => $release['title'],
                        'cover_art_url' => $rgId ? CoverArtService::url($rgId) : null,
                    ] : null,
                    'artists' => collect($rec['artist-credit'] ?? [])
                        ->filter(fn ($credit) => is_array($credit))
                        ->map(fn (array $credit) => [
                            'mbid'        => $credit['artist']['id'] ?? null,
                            'name'        => $credit['artist']['name'] ?? $credit['name'] ?? null,
                            'join_phrase' => $credit['joinphrase'] ?? null,
                        ])->values()->all(),
                ];
            })
            ->keyBy('mbid');
    }

    private function cacheKey(string $type, string $query, int $limit): string
    {
        return "mb_search:{$type}:" . md5("{$query}:{$limit}");
    }
}
