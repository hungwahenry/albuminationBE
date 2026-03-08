<?php

namespace App\Services;

use App\Models\Track;
use App\Models\User;

class TrackFavouriteService
{
    public function toggle(User $user, Track $track): array
    {
        $exists = $user->favouriteTracks()->where('track_id', $track->id)->exists();

        if ($exists) {
            $user->favouriteTracks()->detach($track->id);
            return ['is_favourited' => false];
        }

        $user->favouriteTracks()->attach($track->id);
        return ['is_favourited' => true];
    }
}
