<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRotationRequest;
use App\Http\Requests\UpdateRotationRequest;
use App\Http\Resources\RotationResource;
use App\Models\Rotation;
use App\Services\RotationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RotationController extends Controller
{
    use ApiResponse;

    public function __construct(private RotationService $service) {}

    private function eagerLoadFor(Rotation $rotation): array
    {
        return $rotation->type === 'album'
            ? ['vibetags', 'items.album.artists', 'user.profile']
            : ['vibetags', 'items.track.artists', 'items.track.album', 'user.profile'];
    }

    public function index(Request $request): JsonResponse
    {
        $rotations = $request->user()
            ->rotations()
            ->with(['vibetags', 'user.profile'])
            ->latest()
            ->paginate(20);

        return $this->success(RotationResource::collection($rotations)->response()->getData(true));
    }

    public function store(StoreRotationRequest $request): JsonResponse
    {
        $rotation = $this->service->create(
            $request->user(),
            $request->validated(),
        );

        return $this->success(new RotationResource($rotation->load('user.profile')), 'Rotation created', 201);
    }

    public function show(Request $request, Rotation $rotation): JsonResponse
    {
        if (!$rotation->is_public && !$rotation->isOwnedBy($request->user()->id)) {
            return $this->error('Rotation not found', 404);
        }

        $rotation->load($this->eagerLoadFor($rotation));

        return $this->success(new RotationResource($rotation));
    }

    public function update(UpdateRotationRequest $request, Rotation $rotation): JsonResponse
    {
        if (!$rotation->isOwnedBy($request->user()->id)) {
            return $this->error('Forbidden', 403);
        }

        $rotation = $this->service->update($rotation, $request->validated());

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }

    public function destroy(Request $request, Rotation $rotation): JsonResponse
    {
        if (!$rotation->isOwnedBy($request->user()->id)) {
            return $this->error('Forbidden', 403);
        }

        $this->service->delete($rotation);

        return $this->success(null, 'Rotation deleted');
    }

    public function publish(Request $request, Rotation $rotation): JsonResponse
    {
        if (!$rotation->isOwnedBy($request->user()->id)) {
            return $this->error('Forbidden', 403);
        }

        if ($rotation->isPublished()) {
            return $this->error('Rotation is already published', 422);
        }

        $rotation = $this->service->publish($rotation);

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }

    public function redraft(Request $request, Rotation $rotation): JsonResponse
    {
        if (!$rotation->isOwnedBy($request->user()->id)) {
            return $this->error('Forbidden', 403);
        }

        if ($rotation->isDraft()) {
            return $this->error('Rotation is already a draft', 422);
        }

        $rotation = $this->service->redraft($rotation);

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }
}
