<?php

namespace App\Services\MusicBrainz;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;

class MusicBrainzService
{
    public function __construct(private MusicBrainzClient $client) {}

    public function fetchArtist(string $mbid): ?Artist
    {
        $data = $this->client->lookup('artist', $mbid);

        if (!$data || !isset($data['id'])) {
            return null;
        }

        return $this->storeArtist($data);
    }

    public function searchArtists(string $query, int $limit = 25, int $offset = 0): ?array
    {
        return $this->client->search('artist', $query, $limit, $offset);
    }

    /**
     * Fetch a single album from MusicBrainz by MBID and store it.
     */
    public function fetchAlbum(string $mbid): ?Album
    {
        $data = $this->client->lookup('release-group', $mbid, ['artist-credits']);

        if (!$data || !isset($data['id'])) {
            return null;
        }

        return $this->storeAlbum($data);
    }

    /**
     * Fetch all albums for an artist from MusicBrainz and store them.
     */
    public function fetchArtistAlbums(Artist $artist): void
    {
        $offset = 0;
        $limit = 100;

        do {
            $data = $this->client->browse(
                'release-group',
                'artist',
                $artist->mbid,
                $limit,
                $offset,
                ['artist-credits'],
                // Filter at API level: only Album/EP primary types, exclude promo/bootleg-only groups
                ['type' => 'album|ep', 'release-group-status' => 'website-default'],
            );

            if (!$data || !isset($data['release-groups'])) {
                break;
            }

            foreach ($data['release-groups'] as $rg) {
                // Still skip secondary types (live albums, compilations, remixes, etc.)
                if (!empty($rg['secondary-types'] ?? [])) {
                    continue;
                }

                $this->storeAlbum($rg);
            }

            $total = $data['release-group-count'] ?? 0;
            $offset += $limit;
        } while ($offset < $total);
    }

    /**
     * Search MusicBrainz for albums (raw results, not stored).
     */
    public function searchAlbums(string $query, int $limit = 25, int $offset = 0): ?array
    {
        return $this->client->search('release-group', $query, $limit, $offset);
    }

    /**
     * Search MusicBrainz for tracks (raw results, not stored).
     */
    public function searchTracks(string $query, int $limit = 25, int $offset = 0): ?array
    {
        return $this->client->search('recording', $query, $limit, $offset);
    }

    public function lookupRecording(string $mbid): ?array
    {
        return $this->client->lookup('recording', $mbid, ['releases', 'artist-credits', 'release-groups']);
    }

    /**
     * Fetch all tracks for an album from MusicBrainz and store them.
     */
    public function fetchAlbumTracks(Album $album): void
    {
        // Fetch official releases only — avoids promos, bootlegs, and regional oddities
        $releasesData = $this->client->browse(
            'release',
            'release-group',
            $album->mbid,
            100,
            0,
            [],
            ['status' => 'official'],
        );

        $releases = $releasesData['releases'] ?? [];

        // Fall back to any release if no official ones found (rare, but possible for older releases)
        if (empty($releases)) {
            $fallback = $this->client->browse('release', 'release-group', $album->mbid, 1, 0);
            $releases = $fallback['releases'] ?? [];
        }

        if (empty($releases)) {
            return;
        }

        // MB returns official releases sorted by date ascending — first is the original release
        $releaseData = $this->client->lookup(
            'release',
            $releases[0]['id'],
            ['recordings', 'artist-credits'],
        );

        if (!$releaseData || !isset($releaseData['media'])) {
            return;
        }

        foreach ($releaseData['media'] as $medium) {
            foreach ($medium['tracks'] ?? [] as $trackData) {
                $recording = $trackData['recording'] ?? [];

                $track = Track::updateOrCreate(
                    ['mbid' => $recording['id'] ?? $trackData['id']],
                    [
                        'title' => $trackData['title'],
                        'length' => $trackData['length'] ?? $recording['length'] ?? null,
                        'position' => $trackData['position'] ?? null,
                        'album_id' => $album->id,
                    ],
                );

                $credits = $recording['artist-credit'] ?? $trackData['artist-credit'] ?? [];
                $this->syncArtistCredits($track, $credits);
            }
        }
    }

    /**
     * Store an artist from raw MusicBrainz data.
     */
    public function storeArtist(array $data): Artist
    {
        return Artist::updateOrCreate(
            ['mbid' => $data['id']],
            [
                'name' => $data['name'],
                'sort_name' => $data['sort-name'] ?? null,
                'type' => $data['type'] ?? null,
                'country' => $data['country'] ?? null,
                'disambiguation' => $data['disambiguation'] ?? null,
                'begin_date' => $this->parsePartialDate($data['life-span']['begin'] ?? null),
                'end_date' => $this->parsePartialDate($data['life-span']['end'] ?? null),
            ],
        );
    }

    private function storeAlbum(array $data): Album
    {
        $album = Album::updateOrCreate(
            ['mbid' => $data['id']],
            [
                'title' => $data['title'],
                'type' => $data['primary-type'] ?? 'Album',
                'release_date' => $this->parsePartialDate($data['first-release-date'] ?? null),
            ],
        );

        $this->syncArtistCredits($album, $data['artist-credit'] ?? []);

        return $album;
    }

    public function syncArtistCredits(Album|Track $entity, array $credits): void
    {
        $pivotData = [];

        foreach ($credits as $order => $credit) {
            $artistData = $credit['artist'] ?? null;

            if (!$artistData || !isset($artistData['id'])) {
                continue;
            }

            $artist = Artist::updateOrCreate(
                ['mbid' => $artistData['id']],
                [
                    'name' => $artistData['name'],
                    'sort_name' => $artistData['sort-name'] ?? null,
                    'disambiguation' => $artistData['disambiguation'] ?? null,
                ],
            );

            $pivotData[$artist->id] = [
                'join_phrase' => $credit['joinphrase'] ?? null,
                'order' => $order,
            ];
        }

        if (!empty($pivotData)) {
            $entity->artists()->syncWithoutDetaching($pivotData);
        }
    }

    private function parsePartialDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        return match (strlen($date)) {
            4 => "{$date}-01-01",
            7 => "{$date}-01",
            default => $date,
        };
    }
}
