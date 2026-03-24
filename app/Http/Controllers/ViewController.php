<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Profile;
use App\Models\Rotation;
use App\Services\AlbumService;
use App\Services\ViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function __construct(
        private ViewService $viewService,
        private AlbumService $albumService,
    ) {}

    public function storeRotationView(Request $request, Rotation $rotation): JsonResponse
    {
        $user = $request->user();

        if ($user->id !== $rotation->user_id) {
            $this->viewService->track($user, $rotation);
        }

        return response()->json(['views_count' => $rotation->fresh()->views_count]);
    }

    public function storeAlbumView(Request $request, string $slug): JsonResponse
    {
        $album = $this->albumService->show($slug);

        if (!$album) {
            return response()->json(['message' => 'Album not found.'], 404);
        }

        $this->viewService->track($request->user(), $album);

        return response()->json(['views_count' => $album->fresh()->views_count]);
    }

    public function storeArtistView(Request $request, string $slug): JsonResponse
    {
        $artist = Artist::where('slug', $slug)->orWhere('mbid', $slug)->first();

        if (!$artist) {
            return response()->json(['message' => 'Artist not found.'], 404);
        }

        $this->viewService->track($request->user(), $artist);

        return response()->json(['views_count' => $artist->fresh()->views_count]);
    }

    public function storeProfileView(Request $request, string $username): JsonResponse
    {
        $profile = Profile::where('username', $username)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found.'], 404);
        }

        // Don't count own profile views
        if ($request->user()->id !== $profile->user_id) {
            $this->viewService->track($request->user(), $profile);
        }

        return response()->json(['views_count' => $profile->fresh()->views_count]);
    }
}
