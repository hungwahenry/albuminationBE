<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid' => $this['mbid'],
            'title' => $this['title'],
            'length' => $this['length'] ?? null,
            'album' => $this['album'] ?? null,
            'artists' => $this['artists'] ?? [],
        ];
    }
}
