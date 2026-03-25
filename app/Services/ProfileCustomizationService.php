<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Track;
use App\Models\User;

class ProfileCustomizationService
{
    public function __construct(private AlbumService $albumService) {}

    public function setHeaderAlbum(User $user, ?string $mbid): User
    {
        $albumId = null;

        if ($mbid) {
            $album = $this->albumService->show($mbid, $user);
            $albumId = $album?->id;
        }

        $user->profile->update(['header_album_id' => $albumId]);

        return $user->load(['profile.headerAlbum.artists', 'profile.pinnedRotation', 'profile.currentVibe.artists']);
    }

    public function setPinnedRotation(User $user, ?int $rotationId): User
    {
        $user->profile->update(['pinned_rotation_id' => $rotationId]);

        return $user->load(['profile.headerAlbum.artists', 'profile.pinnedRotation', 'profile.currentVibe.artists']);
    }

    public function setCurrentVibe(User $user, ?string $type, ?string $mbid): User
    {
        $vibeType = null;
        $vibeId = null;

        if ($type && $mbid) {
            if ($type === 'album') {
                $album = $this->albumService->show($mbid, $user);
                $vibeType = $album ? Album::class : null;
                $vibeId = $album?->id;
            } elseif ($type === 'track') {
                $track = Track::where('mbid', $mbid)->first();
                $vibeType = $track ? Track::class : null;
                $vibeId = $track?->id;
            }
        }

        $user->profile->update([
            'current_vibe_type' => $vibeType,
            'current_vibe_id' => $vibeId,
        ]);

        return $user->load(['profile.headerAlbum.artists', 'profile.pinnedRotation', 'profile.currentVibe.artists']);
    }
}
