<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Take;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FriendsTakesResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $followingIds = $user->following()->pluck('following_id');

        return Take::with(['user.profile', 'album.artists'])
            ->whereIn('user_id', $followingIds)
            ->where('is_deleted', false)
            ->latest()
            ->paginate($perPage);
    }
}
