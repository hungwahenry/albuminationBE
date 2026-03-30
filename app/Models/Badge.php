<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'rarity',
        'trigger',
        'criteria',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'active'   => 'boolean',
        ];
    }

    public function rarityConfig(): BelongsTo
    {
        return $this->belongsTo(BadgeRarityConfig::class, 'rarity', 'key');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
