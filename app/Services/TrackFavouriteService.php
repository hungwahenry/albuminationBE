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
            $track->decrement('favourites_count');
            return ['is_favourited' => false, 'favourites_count' => max(0, $track->favourites_count)];
        }

        $user->favouriteTracks()->attach($track->id);
        $track->increment('favourites_count');
        return ['is_favourited' => true, 'favourites_count' => $track->favourites_count];
    }
}
