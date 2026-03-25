<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug'        => $this->slug,
            'name'        => $this->name,
            'description' => $this->description,
            'icon'        => $this->icon,
            'rarity'      => $this->rarity,
            'earned_at'   => $this->pivot->earned_at,
        ];
    }
}
