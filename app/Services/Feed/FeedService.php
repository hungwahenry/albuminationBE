<?php

namespace App\Services\Feed;

use App\Models\FeedSection;
use App\Models\User;
use App\Services\Feed\Resolvers\FeaturedAlbumsResolver;
use App\Services\Feed\Resolvers\FriendsTakesResolver;
use App\Services\Feed\Resolvers\FriendsRotationsResolver;
use App\Services\Feed\Resolvers\LatestRotationsResolver;
use App\Services\Feed\Resolvers\LatestTakesResolver;
use App\Services\Feed\Resolvers\NewToAlbuminationResolver;
use App\Services\Feed\Resolvers\PopularRotationsResolver;
use App\Services\Feed\Resolvers\RotationsByVibeResolver;
use App\Services\Feed\Resolvers\SuggestedUsersResolver;
use App\Services\Feed\Resolvers\TopAlbumsResolver;
use App\Services\Feed\Resolvers\TopTakesResolver;
use App\Services\Feed\Resolvers\TrendingAlbumsResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class FeedService
{
    public function __construct(
        private FeaturedAlbumsResolver   $featuredAlbums,
        private NewToAlbuminationResolver $newToAlbumination,
        private TrendingAlbumsResolver    $trendingAlbums,
        private TopAlbumsResolver         $topAlbums,
        private FriendsRotationsResolver  $friendsRotations,
        private FriendsTakesResolver      $friendsTakes,
        private PopularRotationsResolver  $popularRotations,
        private LatestRotationsResolver   $latestRotations,
        private TopTakesResolver          $topTakes,
        private LatestTakesResolver       $latestTakes,
        private RotationsByVibeResolver   $rotationsByVibe,
        private SuggestedUsersResolver    $suggestedUsers,
    ) {}

    public function resolve(FeedSection $section, User $user, int $perPage): LengthAwarePaginator
    {
        return match ($section->type) {
            'featured_albums'     => $this->featuredAlbums->resolve($section, $user, $perPage),
            'new_to_albumination' => $this->newToAlbumination->resolve($section, $user, $perPage),
            'trending_albums'     => $this->trendingAlbums->resolve($section, $user, $perPage),
            'top_albums'          => $this->topAlbums->resolve($section, $user, $perPage),
            'friends_rotations'   => $this->friendsRotations->resolve($section, $user, $perPage),
            'friends_takes'       => $this->friendsTakes->resolve($section, $user, $perPage),
            'popular_rotations'   => $this->popularRotations->resolve($section, $user, $perPage),
            'latest_rotations'    => $this->latestRotations->resolve($section, $user, $perPage),
            'top_takes'           => $this->topTakes->resolve($section, $user, $perPage),
            'latest_takes'        => $this->latestTakes->resolve($section, $user, $perPage),
            'rotations_by_vibe'   => $this->rotationsByVibe->resolve($section, $user, $perPage),
            'suggested_users'     => $this->suggestedUsers->resolve($section, $user, $perPage),
            default               => throw new InvalidArgumentException("Unknown feed section type: {$section->type}"),
        };
    }
}
