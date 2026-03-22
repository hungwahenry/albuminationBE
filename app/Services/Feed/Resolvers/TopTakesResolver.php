<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Take;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TopTakesResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $days  = $section->config['days'] ?? 7;

        return Take::with(['user.profile', 'album.artists'])
            ->where('is_deleted', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('agrees_count')
            ->paginate($perPage);
    }
}
