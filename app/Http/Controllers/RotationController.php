<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListRotationsRequest;
use App\Http\Requests\StoreRotationRequest;
use App\Http\Requests\UpdateRotationRequest;
use App\Http\Resources\RotationResource;
use App\Models\Album;
use App\Models\Rotation;
use App\Models\Track;
use App\Services\RotationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

    public function index(ListRotationsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $containsMbid = $validated['contains_mbid'] ?? null;

        $query = $request->user()
            ->rotations()
            ->with(['vibetags', 'user.profile'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['sort'] ?? null, function ($q, $sort) {
                match ($sort) {
                    'oldest' => $q->oldest(),
                    'alphabetical' => $q->orderBy('title'),
                    'recently_updated' => $q->latest('updated_at'),
                    default => $q->latest(),
                };
            }, fn ($q) => $q->latest());

        if ($containsMbid) {
            $type = $validated['type'] ?? null;
            $entityId = null;

            if ($type === 'album') {
                $entityId = Album::where('mbid', $containsMbid)->value('id');
            } elseif ($type === 'track') {
                $entityId = Track::where('mbid', $containsMbid)->value('id');
            }

            if ($entityId) {
                $fk = $type === 'album' ? 'album_id' : 'track_id';
                $query->withExists([
                    'items as contains_item' => fn ($q) => $q->where($fk, $entityId),
                ]);
            }
        }

        $rotations = $query->paginate(20);

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
        Gate::authorize('view', $rotation);

        $rotation->load($this->eagerLoadFor($rotation));

        return $this->success(new RotationResource($rotation));
    }

    public function update(UpdateRotationRequest $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('update', $rotation);

        $rotation = $this->service->update($rotation, $request->validated());

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }

    public function destroy(Request $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('delete', $rotation);

        $this->service->delete($rotation);

        return $this->success(null, 'Rotation deleted');
    }

    public function publish(Request $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('publish', $rotation);

        if ($rotation->isPublished()) {
            return $this->error('Rotation is already published', 422);
        }

        if ($rotation->items()->count() === 0) {
            return $this->error('Cannot publish a rotation with no items', 422);
        }

        $rotation = $this->service->publish($rotation);

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }

    public function redraft(Request $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('redraft', $rotation);

        if ($rotation->isDraft()) {
            return $this->error('Rotation is already a draft', 422);
        }

        $rotation = $this->service->redraft($rotation);

        return $this->success(new RotationResource($rotation->load($this->eagerLoadFor($rotation))));
    }
}
