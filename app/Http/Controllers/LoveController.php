<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\LoveService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoveController extends Controller
{
    use ApiResponse;

    public function __construct(private LoveService $loveService) {}

    public function toggleAlbum(Request $request, string $mbid): JsonResponse
    {
        $album = Album::where('mbid', $mbid)->first();

        if (!$album) {
            return $this->error('Album not found', 404);
        }

        $result = $this->loveService->toggle($request->user(), $album);

        return $this->success($result);
    }
}
