<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Take;
use App\Models\TakeReply;
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

    public function toggleReply(Request $request, string $mbid, Take $take, TakeReply $reply): JsonResponse
    {
        if ($reply->take_id !== $take->id) {
            return $this->error('Reply not found', 404);
        }

        $result = $this->loveService->toggle($request->user(), $reply);

        return $this->success($result);
    }
}
