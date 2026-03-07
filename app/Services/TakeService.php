<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Take;
use App\Models\User;

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

        $take = Take::create([
            'user_id'  => $user->id,
            'album_id' => $album->id,
            'rating'   => $rating,
            'body'     => $body,
        ]);

        $album->increment('takes_count');

        return $take->load('user.profile');
    }

    /**
     * Update a take. Allowed only once — 422 if already edited.
     */
    public function update(Take $take, string $rating, string $body): Take
    {
        if ($take->edited_at !== null) {
            abort(422, 'You can only edit your take once.');
        }

        $take->update([
            'rating'    => $rating,
            'body'      => $body,
            'edited_at' => now(),
        ]);

        return $take->load('user.profile');
    }

    /**
     * Soft-delete a take — body/rating hidden, conversation preserved.
     */
    public function delete(Take $take): void
    {
        $take->update(['is_deleted' => true]);
        $take->album->decrement('takes_count');
    }
}
