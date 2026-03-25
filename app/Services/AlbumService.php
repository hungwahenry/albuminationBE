<?php

namespace App\Services;

use App\Jobs\EvaluateBadgesJob;
use App\Models\Album;
use App\Models\User;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Support\Str;

class AlbumService
{
    public function __construct(private MusicBrainzService $musicBrainz) {}

    /**
     * Find an album by slug or MBID, fetching from MusicBrainz if not stored locally.
     * Also fetches tracks if the album has none.
     */
    public function show(string $slug, ?User $seedingUser = null): ?Album
    {
        $album = Album::with(['artists', 'tracks.artists'])
            ->where('slug', $slug)
            ->orWhere('mbid', $slug)
            ->first();

        if (!$album) {
            if (!Str::isUuid($slug)) {
                return null;
            }

            $album = $this->musicBrainz->fetchAlbum($slug);

            if (!$album) {
                return null;
            }

            if ($album->wasRecentlyCreated && $seedingUser) {
                $album->update(['seeded_by_user_id' => $seedingUser->id]);
                EvaluateBadgesJob::dispatch('album_seeded', $seedingUser->id, Album::class, $album->id);
            }
        }

        if ($album->tracks()->doesntExist()) {
            $this->musicBrainz->fetchAlbumTracks($album);
        }

        return $album->load(['artists', 'tracks.artists']);
    }
}
