<?php

namespace App\Http\Controllers;

use App\Http\Resources\RotationResource;
use App\Http\Resources\VibetagResource;
use App\Models\Vibetag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VibetagController extends Controller
{
    use ApiResponse;

    public function show(string $name): JsonResponse
    {
        $vibetag = Vibetag::where('name', strtolower(trim($name)))->first();

        if (!$vibetag) {
            return $this->error('Vibetag not found', 404);
        }

        return $this->success(new VibetagResource($vibetag));
    }

    public function rotations(Request $request, string $name): JsonResponse
    {
        $vibetag = Vibetag::where('name', strtolower(trim($name)))->first();

        if (!$vibetag) {
            return $this->error('Vibetag not found', 404);
        }

        $paginated = $vibetag->rotations()
            ->with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->where('is_public', true)
            ->latest('published_at')
            ->paginate(20);

        return $this->success(
            RotationResource::collection($paginated)->response()->getData(true)
        );
    }
}
