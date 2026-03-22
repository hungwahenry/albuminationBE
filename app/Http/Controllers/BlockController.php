<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlockedUserResource;
use App\Models\Profile;
use App\Services\BlockService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    use ApiResponse;

    public function __construct(private BlockService $blockService) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->blocks()
            ->with('blockedUser.profile')
            ->latest('created_at')
            ->paginate(30);

        $paginator->setCollection(
            $paginator->getCollection()->map->blockedUser
        );

        return $this->success(
            BlockedUserResource::collection($paginator)->response()->getData(true)
        );
    }

    public function store(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $target = $profile->user;

        if ($request->user()->id === $target->id) {
            return $this->error('You cannot block yourself', 422);
        }

        $this->blockService->block($request->user(), $target);

        return $this->success(null, 'User blocked');
    }

    public function destroy(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $this->blockService->unblock($request->user(), $profile->user);

        return $this->success(null, 'User unblocked');
    }
}
