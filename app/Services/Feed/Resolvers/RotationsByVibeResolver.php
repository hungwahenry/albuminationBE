<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Rotation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as EmptyPaginator;
use Illuminate\Support\Collection;

class RotationsByVibeResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        $vibetagId = $section->config['vibetag_id'] ?? null;

        if (!$vibetagId) {
            return new EmptyPaginator(new Collection(), 0, $perPage);
        }

        return Rotation::with(['vibetags', 'user.profile'])
            ->whereHas('vibetags', fn ($q) => $q->where('vibetags.id', $vibetagId))
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->paginate($perPage);
    }
}
