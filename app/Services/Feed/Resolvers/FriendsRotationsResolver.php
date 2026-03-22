<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Rotation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FriendsRotationsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $followingIds = $user->following()->pluck('following_id');

        return Rotation::with(['vibetags', 'user.profile'])
            ->whereIn('user_id', $followingIds)
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->paginate($perPage);
    }
}
