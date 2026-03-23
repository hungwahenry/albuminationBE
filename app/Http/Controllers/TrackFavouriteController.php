<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Services\TrackFavouriteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackFavouriteController extends Controller
{
    use ApiResponse;

    public function __construct(private TrackFavouriteService $service) {}

    public function toggle(Request $request, string $slug, int $trackId): JsonResponse
    {
        $track = Track::whereHas('album', fn ($q) => $q->where('slug', $slug)->orWhere('mbid', $slug))
            ->where('id', $trackId)
            ->first();

        if (!$track) {
            return $this->error('Track not found', 404);
        }

        $result = $this->service->toggle($request->user(), $track);

        return $this->success($result);
    }
}
