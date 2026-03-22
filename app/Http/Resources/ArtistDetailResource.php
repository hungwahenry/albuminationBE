<?php

namespace App\Http\Resources;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Take;
use App\Services\CoverArtService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ArtistDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Artist $artist */
        $artist = $this->resource;

        $albumModels = $artist->albums()
            ->orderByRaw('ISNULL(release_date), release_date DESC')
            ->get();

        $albumIds = $albumModels->pluck('id');

        $albums = $albumModels->map(fn (Album $album) => [
            'mbid'          => $album->mbid,
            'title'         => $album->title,
            'type'          => $album->type,
            'release_date'  => $album->release_date?->toDateString(),
            'cover_art_url' => $album->mbid ? CoverArtService::url($album->mbid) : null,
            'loves_count'   => $album->loves_count,
            'takes_count'   => $album->takes_count,
        ]);

        $recentTakes = Take::whereIn('album_id', $albumIds)
            ->where('is_deleted', false)
            ->with(['user.profile', 'album'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Take $take) => [
                'id'         => $take->id,
                'rating'     => $take->rating,
                'body'       => $take->body,
                'created_at' => $take->created_at->toISOString(),
                'user'       => [
                    'username'     => $take->user->profile->username,
                    'display_name' => $take->user->profile->display_name,
                    'avatar'       => $take->user->profile->avatar,
                ],
                'album'      => [
                    'mbid'          => $take->album->mbid,
                    'title'         => $take->album->title,
                    'cover_art_url' => $take->album->mbid ? CoverArtService::url($take->album->mbid) : null,
                ],
            ]);

        $totalTracks = $artist->tracks()->count();

        return [
            'slug'           => $artist->slug,
            'mbid'           => $artist->mbid,
            'name'           => $artist->name,
            'type'           => $artist->type,
            'country'        => $artist->country,
            'disambiguation' => $artist->disambiguation,
            'begin_date'     => $artist->begin_date?->toDateString(),
            'end_date'       => $artist->end_date?->toDateString(),
            'image_url'      => $artist->image_url,
            'stans_count'    => $artist->stans_count,
            'is_stanned'     => Auth::check() ? $artist->isStannedBy(Auth::id()) : false,
            'stats'          => [
                'total_loves'  => $albums->sum('loves_count'),
                'total_takes'  => $albums->sum('takes_count'),
                'total_tracks' => $totalTracks,
            ],
            'recent_takes'   => $recentTakes,
            'albums'         => $albums,
        ];
    }
}
