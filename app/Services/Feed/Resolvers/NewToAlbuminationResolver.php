<?php

namespace App\Services\Feed\Resolvers;

use App\Models\Album;
use App\Models\FeedSection;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewToAlbuminationResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        return Album::with('artists')
            ->latest()
            ->paginate($perPage);
    }
}
