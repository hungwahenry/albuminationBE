<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Rotation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LatestRotationsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        return Rotation::with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->paginate($perPage);
    }
}
