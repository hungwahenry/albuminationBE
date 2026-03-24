<?php

namespace App\Services;

use App\Events\StanCreated;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StanService
{
    /**
     * Toggle a stan for an artist.
    */
    public function toggle(User $user, Artist $artist): array
    {
        return DB::transaction(function () use ($user, $artist) {
            $isStanned = $artist->stans()->where('user_id', $user->id)->exists();

            if ($isStanned) {
                $artist->stans()->detach($user->id);
                $artist->decrement('stans_count');
                $user->profile->decrement('stans_count');

                return [
                    'is_stanned'  => false,
                    'stans_count' => max(0, $artist->stans_count),
                ];
            }

            $artist->stans()->attach($user->id);
            $artist->increment('stans_count');
            $user->profile->increment('stans_count');

            StanCreated::dispatch($user, $artist);

            return [
                'is_stanned'  => true,
                'stans_count' => $artist->stans_count,
            ];
        });
    }
}
