<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mbid'           => $this['mbid'],
            'slug'           => $this['slug'] ?? null,
            'name'           => $this['name'],
            'type'           => $this['type'] ?? null,
            'country'        => $this['country'] ?? null,
            'disambiguation' => $this['disambiguation'] ?? null,
        ];
    }
}
