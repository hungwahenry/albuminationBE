<?php

namespace App\Services;

use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TrackFavouriteService
{
    public function toggle(User $user, Track $track): array
    {
        return DB::transaction(function () use ($user, $track) {
            $exists = $user->favouriteTracks()->where('track_id', $track->id)->exists();

            if ($exists) {
                $user->favouriteTracks()->detach($track->id);
                $track->decrement('favourites_count');
                return ['is_favourited' => false, 'favourites_count' => max(0, $track->favourites_count)];
            }

            $user->favouriteTracks()->attach($track->id);
            $track->increment('favourites_count');
            return ['is_favourited' => true, 'favourites_count' => $track->favourites_count];
        });
    }
}
