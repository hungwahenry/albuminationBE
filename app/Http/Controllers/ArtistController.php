<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArtistAlbumResource;
use App\Http\Resources\ArtistDetailResource;
use App\Jobs\SyncArtistAlbumsJob;
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
        // Support lookup by slug or mbid (for artists not yet in DB)
        $artist = Artist::where('slug', $slug)
            ->orWhere('mbid', $slug)
            ->first();

        if (!$artist) {
            // Not in DB yet — fetch from MusicBrainz and create
            $artist = $this->musicBrainz->fetchArtist($slug);

            if (!$artist) {
                return $this->error('Artist not found.', 404);
            }
        }

        if ($artist->mbid && (!$artist->albums_synced_at || $artist->albums_synced_at->lt(now()->subDay()))) {
            SyncArtistAlbumsJob::dispatch($artist->id);
        }

        return $this->success(new ArtistDetailResource($artist));
    }

    public function albums(string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->orWhere('mbid', $slug)->first();

        if (!$artist) {
            return $this->error('Artist not found.', 404);
        }

        // Never synced — fetch synchronously so the caller waits for real data
        if ($artist->mbid && !$artist->albums_synced_at) {
            $this->musicBrainz->fetchArtistAlbums($artist);
            $artist->update(['albums_synced_at' => now()]);
        }

        $albums = $artist->albums()->orderByRaw('ISNULL(release_date), release_date DESC')->get();

        return $this->success(ArtistAlbumResource::collection($albums));
    }

    public function stan(Request $request, string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->orWhere('mbid', $slug)->firstOrFail();

        $result = $this->stanService->toggle($request->user(), $artist);

        return $this->success($result);
    }
}
