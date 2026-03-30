<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rarityConfig = $this->rarityConfig;

        return [
            'slug'          => $this->slug,
            'name'          => $this->name,
            'description'   => $this->description,
            'icon_url'      => $this->icon ? Storage::disk('public')->url($this->icon) : null,
            'rarity'        => $this->rarity,
            'rarity_config' => $rarityConfig ? [
                'key'            => $rarityConfig->key,
                'label'          => $rarityConfig->label,
                'color'          => $rarityConfig->color,
                'bg_color'       => $rarityConfig->bg_color,
                'bg_light_color' => $rarityConfig->bg_light_color,
            ] : null,
            'earned_at'     => $this->pivot?->earned_at,
        ];
    }
}
