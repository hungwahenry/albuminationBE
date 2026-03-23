<?php

namespace App\Http\Resources;

use App\Services\CoverArtService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid'          => $this->mbid,
            'title'         => $this->title,
            'type'          => $this->type,
            'release_date'  => $this->release_date?->toDateString(),
            'cover_art_url' => $this->mbid ? CoverArtService::url($this->mbid) : null,
            'loves_count'   => $this->loves_count,
            'takes_count'   => $this->takes_count,
        ];
    }
}
