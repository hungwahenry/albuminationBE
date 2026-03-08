<?php

namespace App\Services;

use App\Models\Follow;
use App\Models\User;

class FollowService
{
    public function toggleFollow(User $follower, User $target): bool
    {
        $existing = Follow::where('follower_id', $follower->id)
            ->where('following_id', $target->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $target->profile->decrement('followers_count');
            $follower->profile->decrement('following_count');

            return false;
        }

        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);

        $target->profile->increment('followers_count');
        $follower->profile->increment('following_count');

        return true;
    }
}
