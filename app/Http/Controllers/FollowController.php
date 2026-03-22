<?php

namespace App\Http\Controllers;

use App\Http\Resources\FollowerResource;
use App\Models\Profile;
use App\Services\FollowService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    use ApiResponse;

    public function __construct(private FollowService $followService) {}

    /**
     * Toggle follow on a user.
     */
    public function toggle(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $target = $profile->user;

        if ($request->user()->id === $target->id) {
            return $this->error('You cannot follow yourself', 422);
        }

        if ($request->user()->hasBlocked($target->id) || $target->hasBlocked($request->user()->id)) {
            return $this->error('Cannot follow this user', 403);
        }

        $isFollowing = $this->followService->toggleFollow($request->user(), $target);

        return $this->success([
            'is_following'    => $isFollowing,
            'followers_count' => $target->profile->fresh()->followers_count,
        ]);
    }

    /**
     * List followers of a user.
     */
    public function followers(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $paginator = $profile->user->followers()
            ->with('follower.profile')
            ->latest('created_at')
            ->paginate(30);

        $paginator->setCollection($paginator->getCollection()->map->follower);

        return $this->success(
            FollowerResource::collection($paginator)->response()->getData(true)
        );
    }

    /**
     * List users that a user is following.
     */
    public function following(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $paginator = $profile->user->following()
            ->with('following.profile')
            ->latest('created_at')
            ->paginate(30);

        $paginator->setCollection($paginator->getCollection()->map->following);

        return $this->success(
            FollowerResource::collection($paginator)->response()->getData(true)
        );
    }

    /**
     * Remove a follower from your own followers list.
     */
    public function removeFollower(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $follower = $profile->user;

        if ($request->user()->id === $follower->id) {
            return $this->error('You cannot remove yourself', 422);
        }

        $this->followService->removeFollower($request->user(), $follower);

        return $this->success([
            'followers_count' => $request->user()->profile->fresh()->followers_count,
        ]);
    }
}
