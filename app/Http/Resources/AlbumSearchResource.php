<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid' => $this['mbid'],
            'title' => $this['title'],
            'type' => $this['type'] ?? null,
            'release_date' => $this['release_date'] ?? null,
            'cover_art_url' => $this['cover_art_url'] ?? null,
            'artists' => $this['artists'] ?? [],
        ];
    }
}
