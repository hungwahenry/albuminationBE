<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BadgeRarityConfig extends Model
{
    protected $table = 'badge_rarities';

    protected $fillable = [
        'key',
        'label',
        'color',
        'bg_color',
        'bg_light_color',
        'sort_order',
    ];

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class, 'rarity', 'key');
    }
}
