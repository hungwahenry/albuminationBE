<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FeedAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid'          => $this->mbid,
            'slug'          => $this->slug,
            'title'         => $this->title,
            'release_date'  => $this->release_date?->toDateString(),
            'cover_art_url' => $this->cover_art_url,
            'loves_count'   => $this->loves_count,
            'takes_count'   => $this->takes_count,
            'is_loved'      => Auth::check() ? $this->isLovedBy(Auth::id()) : false,
            'artists'       => $this->artists->map(fn ($artist) => [
                'mbid'         => $artist->mbid,
                'name'         => $artist->name,
                'join_phrase'  => $artist->pivot->join_phrase,
            ]),
        ];
    }
}
