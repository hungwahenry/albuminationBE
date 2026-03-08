<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicProfileResource;
use App\Models\Rotation;
use App\Services\ProfileCustomizationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileCustomizationController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileCustomizationService $service) {}

    public function setHeaderAlbum(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mbid' => ['nullable', 'string', 'max:36'],
        ]);

        $user = $this->service->setHeaderAlbum($request->user(), $data['mbid'] ?? null);

        return $this->success(new PublicProfileResource($user));
    }

    public function setPinnedRotation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rotation_id' => ['nullable', 'integer'],
        ]);

        $rotationId = $data['rotation_id'] ?? null;

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

    public function setCurrentVibe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', 'string', 'in:album,track'],
            'mbid' => ['nullable', 'string', 'max:36'],
        ]);

        $user = $this->service->setCurrentVibe(
            $request->user(),
            $data['type'] ?? null,
            $data['mbid'] ?? null,
        );

        return $this->success(new PublicProfileResource($user));
    }
}
