<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeedSection;
use App\Models\Take;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LatestTakesResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        return Take::with(['user.profile', 'album.artists'])
            ->where('is_deleted', false)
            ->latest()
            ->paginate($perPage);
    }
}
