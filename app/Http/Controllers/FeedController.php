<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedAlbumResource;
use App\Http\Resources\FeedSectionResource;
use App\Http\Resources\RotationResource;
use App\Http\Resources\SuggestedUserResource;
use App\Http\Resources\TakeResource;
use App\Models\FeedSection;
use App\Services\Feed\FeedService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    use ApiResponse;

    // Maps section types to the resource class used to serialize their items
    private const RESOURCE_MAP = [
        'featured_albums'     => FeedAlbumResource::class,
        'new_to_albumination' => FeedAlbumResource::class,
        'trending_albums'     => FeedAlbumResource::class,
        'top_albums'          => FeedAlbumResource::class,
        'friends_rotations'   => RotationResource::class,
        'latest_rotations'    => RotationResource::class,
        'popular_rotations'   => RotationResource::class,
        'rotations_by_vibe'   => RotationResource::class,
        'friends_takes'       => TakeResource::class,
        'top_takes'           => TakeResource::class,
        'latest_takes'        => TakeResource::class,
        'suggested_users'     => SuggestedUserResource::class,
    ];

    public function __construct(private FeedService $service) {}

    public function sections(Request $request): JsonResponse
    {
        $user           = $request->user();
        $followsNobody  = $user->following()->doesntExist();
        $accountAgeDays = $user->created_at->diffInDays(now());

        $sections = FeedSection::active()
            ->ordered()
            ->get()
            ->filter(fn (FeedSection $s) => $this->sectionIsAccessible($s, $followsNobody, $accountAgeDays))
            ->values();

        return $this->success(FeedSectionResource::collection($sections));
    }

    public function sectionData(Request $request, string $type): JsonResponse
    {
        $section = FeedSection::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$section) {
            return $this->error('Feed section not found', 404);
        }

        $user           = $request->user();
        $followsNobody  = $user->following()->doesntExist();
        $accountAgeDays = $user->created_at->diffInDays(now());

        if (!$this->sectionIsAccessible($section, $followsNobody, $accountAgeDays)) {
            return $this->error('Feed section not available', 403);
        }

        $isCarousel = $request->boolean('carousel');
        $perPage    = $isCarousel
            ? ($section->config['carousel_limit'] ?? 8)
            : ($section->config['per_page'] ?? 20);

        $paginated     = $this->service->resolve($section, $user, $perPage);
        $resourceClass = self::RESOURCE_MAP[$type] ?? null;

        if (!$resourceClass) {
            return $this->error('Unknown feed section type', 422);
        }

        // For featured_albums the paginator contains FeaturedAlbum models;
        // we need to map through to the nested album before passing to the resource.
        if ($type === 'featured_albums') {
            $items = $paginated->getCollection()->map->album;
            $paginated->setCollection($items);
        }

        return $this->success(
            $resourceClass::collection($paginated)->response()->getData(true)
        );
    }

    private function sectionIsAccessible(FeedSection $section, bool $followsNobody, int $accountAgeDays): bool
    {
        if ($section->requires_follows && $followsNobody) {
            return false;
        }

        if ($section->min_account_age_days !== null && $accountAgeDays < $section->min_account_age_days) {
            return false;
        }

        return true;
    }
}
