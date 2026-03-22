<?php

namespace App\Services;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Events\FollowCreated;

class FollowService
{
    public function toggleFollow(User $follower, User $target): bool
    {
        return DB::transaction(function () use ($follower, $target) {
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

            FollowCreated::dispatch($follower, $target);

            return true;
        });
    }

    public function removeFollower(User $user, User $follower): void
    {
        DB::transaction(function () use ($user, $follower) {
            $deleted = Follow::where('follower_id', $follower->id)
                ->where('following_id', $user->id)
                ->delete();

            if ($deleted) {
                $user->profile->decrement('followers_count');
                $follower->profile->decrement('following_count');
            }
        });
    }
}
