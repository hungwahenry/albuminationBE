<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlbumResource;
use App\Services\AlbumService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AlbumController extends Controller
{
    use ApiResponse;

    public function __construct(private AlbumService $albumService) {}

    public function show(string $mbid): JsonResponse
    {
        $album = $this->albumService->show($mbid);

        if (!$album) {
            return $this->error('Album not found', 404);
        }

        return $this->success(new AlbumResource($album));
    }
}
