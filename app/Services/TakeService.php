<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Take;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TakeService
{
    /**
     * Create a take. One per user per album — 422 if already exists.
     */
    public function create(User $user, Album $album, string $rating, string $body): Take
    {
        if (Take::where('user_id', $user->id)->where('album_id', $album->id)->exists()) {
            abort(422, 'You have already posted a take on this album.');
        }

        return DB::transaction(function () use ($user, $album, $rating, $body) {
            $take = Take::create([
                'user_id'  => $user->id,
                'album_id' => $album->id,
                'rating'   => $rating,
                'body'     => $body,
            ]);

            $album->increment('takes_count');
            $album->increment($rating === 'hit' ? 'hits_count' : 'misses_count');

            $user->profile->increment('takes_count');

            Artist::whereIn('id', $album->artists()->pluck('artists.id'))
                ->increment('takes_count');

            return $take->load('user.profile');
        });
    }

    /**
     * Update a take. Allowed only once — 422 if already edited.
     */
    public function update(Take $take, string $rating, string $body): Take
    {
        if ($take->edited_at !== null) {
            abort(422, 'You can only edit your take once.');
        }

        return DB::transaction(function () use ($take, $rating, $body) {
            $oldRating = $take->rating;

            $take->update([
                'rating'    => $rating,
                'body'      => $body,
                'edited_at' => now(),
            ]);

            if ($oldRating !== $rating) {
                $take->album->increment($rating === 'hit' ? 'hits_count' : 'misses_count');
                $take->album->decrement($oldRating === 'hit' ? 'hits_count' : 'misses_count');
            }

            return $take->load('user.profile');
        });
    }

    /**
     * Soft-delete a take — body/rating hidden, conversation preserved.
     */
    public function delete(Take $take): void
    {
        DB::transaction(function () use ($take) {
            $take->update(['is_deleted' => true]);
            $take->album->decrement('takes_count');
            if ($take->rating) {
                $take->album->decrement($take->rating === 'hit' ? 'hits_count' : 'misses_count');
            }

            $take->user->profile->decrement('takes_count');

            Artist::whereIn('id', $take->album->artists()->pluck('artists.id'))
                ->decrement('takes_count');
        });
    }
}
