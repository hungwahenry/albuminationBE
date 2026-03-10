<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddRotationItemRequest;
use App\Http\Requests\ReorderRotationItemsRequest;
use App\Http\Resources\RotationItemResource;
use App\Models\Rotation;
use App\Models\RotationItem;
use App\Services\RotationItemService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RotationItemController extends Controller
{
    use ApiResponse;

    public function __construct(private RotationItemService $service) {}

    public function store(AddRotationItemRequest $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('addItem', $rotation);

        $item = $rotation->type === 'album'
            ? $this->service->addAlbum($rotation, $request->validated('mbid'))
            : $this->service->addTrack($rotation, $request->validated('mbid'));

        return $this->success(new RotationItemResource($item), 'Item added', 201);
    }

    public function destroy(Request $request, Rotation $rotation, RotationItem $item): JsonResponse
    {
        Gate::authorize('removeItem', $rotation);

        if ($item->rotation_id !== $rotation->id) {
            return $this->error('Item not found', 404);
        }

        $this->service->remove($rotation, $item);

        return $this->success(null, 'Item removed');
    }

    public function reorder(ReorderRotationItemsRequest $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('reorder', $rotation);

        $this->service->reorder($rotation, $request->validated('ordered_ids'));

        return $this->success(null, 'Items reordered');
    }
}
