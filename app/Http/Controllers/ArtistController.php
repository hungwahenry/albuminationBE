<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtistDetailResource;
use App\Models\Artist;
use App\Services\MusicBrainz\MusicBrainzService;
use App\Services\StanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    use ApiResponse;

    public function __construct(
        private StanService $stanService,
        private MusicBrainzService $musicBrainz,
    ) {}

    public function show(string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->firstOrFail();

        if ($artist->mbid && (!$artist->albums_synced_at || $artist->albums_synced_at->lt(now()->subDay()))) {
            $this->musicBrainz->fetchArtistAlbums($artist);
            $artist->update(['albums_synced_at' => now()]);
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
