<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PublicProfileResource;
use App\Http\Resources\RotationResource;
use App\Models\Profile;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * View a user's public profile by username.
     */
    public function show(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $user = $profile->user;

        // Eager load the authed user's follow relationship for is_following check
        if ($request->user()->id !== $user->id) {
            $request->user()->load(['following' => fn ($q) => $q->where('following_id', $user->id)]);
        }

        return $this->success(new PublicProfileResource($user));
    }

    /**
     * Get a user's published rotations.
     */
    public function rotations(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $user = $profile->user;

        $query = $user->rotations()
            ->with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at');

        // If viewing own profile, also show private rotations
        if ($request->user()->id === $user->id) {
            $query = $user->rotations()
                ->with(['vibetags', 'user.profile'])
                ->where('status', 'published')
                ->latest('published_at');
        }

        $rotations = $query->paginate(20);

        return $this->success(RotationResource::collection($rotations)->response()->getData(true));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->profile;
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return $this->success(new PublicProfileResource($request->user()->load('profile')));
    }
}
