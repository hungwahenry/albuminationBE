<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SuggestedUsersResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        $excludeIds = $user->following()->pluck('following_id')
            ->push($user->id)
            ->merge($user->blocks()->pluck('blocked_user_id'))
            ->merge($user->blockedBy()->pluck('user_id'))
            ->unique();

        return Profile::with('user')
            ->whereNotIn('user_id', $excludeIds)
            ->orderByDesc('followers_count')
            ->paginate($perPage);
    }
}
