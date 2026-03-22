<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Rotation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PopularRotationsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $days  = $section->config['days'] ?? 7;

        return Rotation::with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->where('published_at', '>=', now()->subDays($days))
            ->orderByDesc('loves_count')
            ->paginate($perPage);
    }
}
