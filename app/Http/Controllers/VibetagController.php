<?php

namespace App\Http\Controllers;

use App\Http\Resources\RotationResource;
use App\Http\Resources\VibetagResource;
use App\Models\Vibetag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class VibetagController extends Controller
{
    use ApiResponse;

    public function show(string $name): JsonResponse
    {
        $vibetag = $this->resolveVibetag($name);

        if (!$vibetag) {
            return $this->error('Vibetag not found', 404);
        }

        return $this->success(new VibetagResource($vibetag));
    }

    public function rotations(string $name): JsonResponse
    {
        $vibetag = $this->resolveVibetag($name);

        if (!$vibetag) {
            return $this->error('Vibetag not found', 404);
        }

        $paginated = $vibetag->rotations()
            ->with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->byRelevance()
            ->paginate(20);

        return $this->success(
            RotationResource::collection($paginated)->response()->getData(true)
        );
    }

    private function resolveVibetag(string $name): ?Vibetag
    {
        return Vibetag::where('name', strtolower(trim($name)))->first();
    }
}
