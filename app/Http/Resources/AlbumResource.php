<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid'        => $this->mbid,
            'slug'        => $this->slug,
            'title'       => $this->title,
            'type'        => $this->type,
            'release_date'=> $this->release_date?->toDateString(),
            'cover_art_url' => $this->cover_art_url,
            'loves_count'     => $this->loves_count,
            'takes_count'     => $this->takes_count,
            'hits_count'      => $this->hits_count,
            'misses_count'    => $this->misses_count,
            'rotations_count' => $this->rotations_count ?? 0,
            'views_count'     => $this->views_count ?? 0,
            'is_loved'    => Auth::check() ? $this->isLovedBy(Auth::id()) : false,
            'artists' => $this->artists->map(fn ($artist) => [
                'mbid'        => $artist->mbid,
                'slug'        => $artist->slug,
                'name'        => $artist->name,
                'join_phrase' => $artist->pivot->join_phrase,
            ]),
            'tracks' => $this->tracks->map(fn ($track) => [
                'id'               => $track->id,
                'mbid'             => $track->mbid,
                'title'            => $track->title,
                'length'           => $track->length,
                'position'         => $track->position,
                'favourites_count' => $track->favourites_count,
                'is_favourited'    => Auth::check() ? $track->isFavouritedBy(Auth::id()) : false,
                'artists'          => $track->artists->map(fn ($artist) => [
                    'mbid'        => $artist->mbid,
                    'slug'        => $artist->slug,
                    'name'        => $artist->name,
                    'join_phrase' => $artist->pivot->join_phrase,
                ]),
            ]),
        ];
    }
}
