<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTakeRequest;
use App\Http\Requests\UpdateTakeRequest;
use App\Http\Resources\TakeResource;
use App\Models\Album;
use App\Models\Take;
use App\Services\TakeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TakeController extends Controller
{
    use ApiResponse;

    public function __construct(private TakeService $takeService) {}

    private function findAlbum(string $slug): ?Album
    {
        return Album::where('slug', $slug)->orWhere('mbid', $slug)->first();
    }

    public function index(Request $request, string $slug): JsonResponse
    {
        $album = $this->findAlbum($slug);

        if (!$album) {
            return $this->error('Album not found', 404);
        }

        $takes = Take::with([
                'user.profile',
                'reactions' => fn ($q) => $q->where('user_id', $request->user()->id),
            ])
            ->where('album_id', $album->id)
            ->orderByRaw('(agrees_count + disagrees_count + replies_count) DESC, created_at DESC')
            ->paginate(20);

        return $this->success(TakeResource::collection($takes)->response()->getData(true));
    }

    public function store(StoreTakeRequest $request, string $slug): JsonResponse
    {
        $album = $this->findAlbum($slug);

        if (!$album) {
            return $this->error('Album not found', 404);
        }

        $take = $this->takeService->create($request->user(), $album, $request->rating, $request->body);

        return $this->success(new TakeResource($take), 'Take posted', 201);
    }

    public function update(UpdateTakeRequest $request, string $slug, Take $take): JsonResponse
    {
        $album = $this->findAlbum($slug);

        if (!$album || $take->album_id !== $album->id) {
            return $this->error('Take not found', 404);
        }

        Gate::authorize('update', $take);

        $take = $this->takeService->update($take, $request->rating, $request->body);

        return $this->success(new TakeResource($take));
    }

    public function destroy(Request $request, string $slug, Take $take): JsonResponse
    {
        $album = $this->findAlbum($slug);

        if (!$album || $take->album_id !== $album->id) {
            return $this->error('Take not found', 404);
        }

        Gate::authorize('delete', $take);

        $this->takeService->delete($take);

        return $this->success(null, 'Take deleted');
    }
}
