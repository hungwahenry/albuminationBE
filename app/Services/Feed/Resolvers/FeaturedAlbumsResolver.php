<?php

namespace App\Services\Feed\Resolvers;

use App\Models\FeaturedAlbum;
use App\Models\FeedSection;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FeaturedAlbumsResolver
{
    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {

        return FeaturedAlbum::with(['album.artists'])
            ->orderBy('sort_order')
            ->paginate($perPage);
    }
}
