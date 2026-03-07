<?php

namespace App\Services;

use App\Models\Album;
use App\Services\MusicBrainz\MusicBrainzService;

class AlbumService
{
    public function __construct(private MusicBrainzService $musicBrainz) {}

    /**
     * Find an album by MBID, fetching from MusicBrainz if not stored locally.
     * Also fetches tracks if the album has none.
     */
    public function show(string $mbid): ?Album
    {
        $album = Album::with(['artists', 'tracks.artists'])->where('mbid', $mbid)->first();

        if (!$album) {
            $album = $this->musicBrainz->fetchAlbum($mbid);

            if (!$album) {
                return null;
            }
        }

        if ($album->tracks()->doesntExist()) {
            $this->musicBrainz->fetchAlbumTracks($album);
        }

        return $album->load(['artists', 'tracks.artists']);
    }
}
