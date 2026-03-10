<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Rotation;
use App\Models\RotationItem;
use App\Models\Track;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Support\Facades\DB;

class RotationItemService
{
    private const MAX_ITEMS = 500;

    public function __construct(
        private AlbumService $albumService,
        private MusicBrainzService $musicBrainz,
    ) {}

    public function addAlbum(Rotation $rotation, string $mbid): RotationItem
    {
        $album = $this->albumService->show($mbid);

        if (!$album) {
            abort(422, 'Album not found');
        }

        if ($rotation->items()->where('album_id', $album->id)->exists()) {
            abort(422, 'Album is already in this rotation');
        }

        if ($rotation->items_count >= self::MAX_ITEMS) {
            abort(422, 'Rotation has reached the maximum of ' . self::MAX_ITEMS . ' items');
        }

        return DB::transaction(function () use ($rotation, $album) {
            $position = $rotation->items()->max('position') + 1;

            $item = $rotation->items()->create([
                'album_id' => $album->id,
                'position' => $position,
            ]);

            $rotation->increment('items_count');

            return $item->load('album.artists');
        });
    }

    public function addTrack(Rotation $rotation, string $mbid): RotationItem
    {
        $track = Track::where('mbid', $mbid)->first();

        if (!$track) {
            $track = $this->resolveTrackFromMusicBrainz($mbid);
        }

        if (!$track) {
            abort(422, 'Track not found');
        }

        if ($rotation->items()->where('track_id', $track->id)->exists()) {
            abort(422, 'Track is already in this rotation');
        }

        if ($rotation->items_count >= self::MAX_ITEMS) {
            abort(422, 'Rotation has reached the maximum of ' . self::MAX_ITEMS . ' items');
        }

        return DB::transaction(function () use ($rotation, $track) {
            $position = $rotation->items()->max('position') + 1;

            $item = $rotation->items()->create([
                'track_id' => $track->id,
                'position' => $position,
            ]);

            $rotation->increment('items_count');

            return $item->load('track.artists', 'track.album');
        });
    }

    public function remove(Rotation $rotation, RotationItem $item): void
    {
        DB::transaction(function () use ($rotation, $item) {
            $removedPosition = $item->position;
            $item->delete();

            $rotation->items()
                ->where('position', '>', $removedPosition)
                ->decrement('position');

            $rotation->decrement('items_count');
        });
    }

    public function reorder(Rotation $rotation, array $orderedIds): void
    {
        foreach ($orderedIds as $position => $itemId) {
            $rotation->items()->where('id', $itemId)->update(['position' => $position + 1]);
        }
    }

    private function resolveTrackFromMusicBrainz(string $mbid): ?Track
    {
        $data = $this->musicBrainz->lookupRecording($mbid);

        if (!$data || !isset($data['id'])) return null;

        // Try to find a release-group so we can store the full album.
        // Prefer Album/EP release-groups over Singles/others.
        $releases = $data['releases'] ?? [];
        $album = null;
        $fallbackRgId = null;

        foreach ($releases as $release) {
            $rgId = $release['release-group']['id'] ?? null;
            if (!$rgId) continue;

            $rgType = $release['release-group']['primary-type'] ?? null;

            if (in_array($rgType, ['Album', 'EP'], true)) {
                $album = $this->albumService->show($rgId);
                if ($album) break;
            } elseif (!$fallbackRgId) {
                $fallbackRgId = $rgId;
            }
        }

        // Fall back to Single/other if no Album/EP was found
        if (!$album && $fallbackRgId) {
            $album = $this->albumService->show($fallbackRgId);
        }

        // Track should now exist from album fetch
        $track = Track::where('mbid', $mbid)->first();

        // Standalone recording — create directly
        if (!$track) {
            $track = Track::create([
                'mbid' => $data['id'],
                'title' => $data['title'],
                'length' => $data['length'] ?? null,
                'album_id' => $album?->id,
            ]);

            $this->musicBrainz->syncArtistCredits($track, $data['artist-credit'] ?? []);
        }

        return $track;
    }
}
