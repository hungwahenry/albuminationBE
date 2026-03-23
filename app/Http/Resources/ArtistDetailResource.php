<?php

namespace App\Http\Resources;

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

        $albumIds = $artist->albums()->pluck('albums.id');

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

        return [
            'slug'              => $artist->slug,
            'mbid'              => $artist->mbid,
            'name'              => $artist->name,
            'type'              => $artist->type,
            'country'           => $artist->country,
            'disambiguation'    => $artist->disambiguation,
            'begin_date'        => $artist->begin_date?->toDateString(),
            'end_date'          => $artist->end_date?->toDateString(),
            'image_url'         => $artist->image_url,
            'stans_count'       => $artist->stans_count ?? 0,
            'is_stanned'        => Auth::check() ? $artist->isStannedBy(Auth::id()) : false,
            'stats'             => [
                'total_loves'  => (int) ($artist->albums()->sum('loves_count') ?? 0),
                'total_takes'  => (int) ($artist->albums()->sum('takes_count') ?? 0),
                'total_tracks' => $artist->tracks()->count(),
            ],
            'recent_takes'      => $recentTakes,
            'albums_synced_at'  => $artist->albums_synced_at?->toISOString(),
        ];
    }
}
