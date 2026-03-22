<?php

namespace App\Services\Feed\Resolvers;

use App\Models\Album;
use App\Models\FeedSection;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TrendingAlbumsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $days  = $section->config['days'] ?? 30;

        return Album::with('artists')
            ->where('updated_at', '>=', now()->subDays($days))
            ->orderByDesc('loves_count')
            ->paginate($perPage);
    }
}
