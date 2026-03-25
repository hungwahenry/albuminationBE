<?php

namespace App\Http\Resources;

use App\Models\BadgeRarityConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $rarityConfig = Cache::remember("badge_rarity:{$this->rarity}", 3600, fn () =>
            BadgeRarityConfig::where('key', $this->rarity)->first()
        );

        return [
            'slug'          => $this->slug,
            'name'          => $this->name,
            'description'   => $this->description,
            'icon'          => $this->icon,
            'rarity'        => $this->rarity,
            'rarity_config' => $rarityConfig ? [
                'key'            => $rarityConfig->key,
                'label'          => $rarityConfig->label,
                'color'          => $rarityConfig->color,
                'bg_color'       => $rarityConfig->bg_color,
                'bg_light_color' => $rarityConfig->bg_light_color,
            ] : null,
            'earned_at'     => $this->pivot->earned_at,
        ];
    }
}
