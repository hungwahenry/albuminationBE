<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetCurrentVibeRequest;
use App\Http\Requests\SetHeaderAlbumRequest;
use App\Http\Requests\SetPinnedRotationRequest;
use App\Http\Resources\PublicProfileResource;
use App\Models\Rotation;
use App\Services\ProfileCustomizationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileCustomizationController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileCustomizationService $service) {}

    public function setHeaderAlbum(SetHeaderAlbumRequest $request): JsonResponse
    {
        $user = $this->service->setHeaderAlbum($request->user(), $request->validated('mbid'));

        return $this->success(new PublicProfileResource($user));
    }

    public function setPinnedRotation(SetPinnedRotationRequest $request): JsonResponse
    {
        $rotationId = $request->validated('rotation_id');

        if ($rotationId) {
            $rotation = Rotation::find($rotationId);

            if (!$rotation || !$rotation->isOwnedBy($request->user()->id)) {
                return $this->error('Rotation not found', 404);
            }

            if (!$rotation->isPublished()) {
                return $this->error('Only published rotations can be pinned', 422);
            }
        }

        $user = $this->service->setPinnedRotation($request->user(), $rotationId);

        return $this->success(new PublicProfileResource($user));
    }

    public function setCurrentVibe(SetCurrentVibeRequest $request): JsonResponse
    {
        $user = $this->service->setCurrentVibe(
            $request->user(),
            $request->validated('type'),
            $request->validated('mbid'),
        );

        return $this->success(new PublicProfileResource($user));
    }
}
