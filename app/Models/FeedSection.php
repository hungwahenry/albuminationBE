<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FeedSection extends Model
{
    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'config',
        'is_active',
        'sort_order',
        'requires_follows',
        'min_account_age_days',
    ];

    protected function casts(): array
    {
        return [
            'config'           => 'array',
            'is_active'        => 'boolean',
            'requires_follows' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
