<?php

namespace App\Http\Controllers;

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

        $followers = $profile->user->followers()
            ->with('follower.profile')
            ->latest('created_at')
            ->paginate(30);

        $items = $followers->getCollection()->map(function ($follow) use ($request) {
            $user = $follow->follower;

            return [
                'id'             => $user->id,
                'username'       => $user->profile->username,
                'display_name'   => $user->profile->display_name,
                'avatar'         => $user->profile->avatar,
                'is_following'   => $request->user()->isFollowing($user->id),
                'is_followed_by' => $user->isFollowing($request->user()->id),
            ];
        });

        return $this->success([
            'data' => $items,
            'meta' => [
                'current_page' => $followers->currentPage(),
                'last_page'    => $followers->lastPage(),
                'per_page'     => $followers->perPage(),
                'total'        => $followers->total(),
            ],
        ]);
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

        $following = $profile->user->following()
            ->with('following.profile')
            ->latest('created_at')
            ->paginate(30);

        $items = $following->getCollection()->map(function ($follow) use ($request) {
            $user = $follow->following;

            return [
                'id'             => $user->id,
                'username'       => $user->profile->username,
                'display_name'   => $user->profile->display_name,
                'avatar'         => $user->profile->avatar,
                'is_following'   => $request->user()->isFollowing($user->id),
                'is_followed_by' => $user->isFollowing($request->user()->id),
            ];
        });

        return $this->success([
            'data' => $items,
            'meta' => [
                'current_page' => $following->currentPage(),
                'last_page'    => $following->lastPage(),
                'per_page'     => $following->perPage(),
                'total'        => $following->total(),
            ],
        ]);
    }
}
