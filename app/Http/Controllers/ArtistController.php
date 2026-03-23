<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtistDetailResource;
use App\Jobs\SyncArtistAlbumsJob;
use App\Models\Artist;
use App\Services\StanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    use ApiResponse;

    public function __construct(private StanService $stanService) {}

    public function show(string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->firstOrFail();

        if ($artist->mbid && (!$artist->albums_synced_at || $artist->albums_synced_at->lt(now()->subDay()))) {
            SyncArtistAlbumsJob::dispatch($artist->id);
        }

        return $this->success(new ArtistDetailResource($artist));
    }

    public function stan(Request $request, string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->firstOrFail();

        $result = $this->stanService->toggle($request->user(), $artist);

        return $this->success($result);
    }
}
