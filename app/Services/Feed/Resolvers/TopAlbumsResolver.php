<?php

namespace App\Services\Feed\Resolvers;

use App\Models\Album;
use App\Models\FeedSection;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TopAlbumsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        return Album::with('artists')
            ->orderByDesc('loves_count')
            ->paginate($perPage);
    }
}
