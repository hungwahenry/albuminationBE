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
            ->unique();

        return Profile::visibleTo($user)
            ->with('user')
            ->whereNotIn('user_id', $excludeIds)
            ->orderByDesc('followers_count')
            ->paginate($perPage);
    }
}
