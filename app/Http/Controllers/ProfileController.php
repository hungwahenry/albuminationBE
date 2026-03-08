<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\PublicProfileResource;
use App\Http\Resources\RotationResource;
use App\Http\Resources\TakeResource;
use App\Services\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileService $profileService) {}

    public function show(Request $request, string $username): JsonResponse
    {
        $profile = $this->profileService->findByUsername($username, [
            'headerAlbum.artists',
            'pinnedRotation',
            'currentVibe.artists',
        ]);

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $user = $profile->user;

        if ($request->user()->id !== $user->id) {
            $request->user()->load(['following' => fn ($q) => $q->where('following_id', $user->id)]);
        }

        return $this->success(new PublicProfileResource($user));
    }

    public function rotations(Request $request, string $username): JsonResponse
    {
        $profile = $this->profileService->findByUsername($username);

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $rotations = $this->profileService->getRotations($profile->user, $request->user());

        return $this->success(RotationResource::collection($rotations)->response()->getData(true));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        $user = $this->profileService->update($request->user(), $data);

        return $this->success(new PublicProfileResource($user));
    }

    public function takes(Request $request, string $username): JsonResponse
    {
        $profile = $this->profileService->findByUsername($username);

        if (!$profile) {
            return $this->error('User not found', 404);
        }

        $takes = $this->profileService->getTakes($profile->user);

        return $this->success(TakeResource::collection($takes)->response()->getData(true));
    }
}
