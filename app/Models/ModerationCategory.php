<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ModerationCategory extends Model
{
    private const CACHE_KEY = 'moderation_categories:active';

    protected $fillable = ['name', 'label', 'threshold', 'enabled', 'sort_order'];

    protected $casts = [
        'threshold'  => 'float',
        'enabled'    => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Categories the service should evaluate. Cached.
     *
     * @return Collection<int, self>
     */
    public static function active(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => self::where('enabled', true)->orderBy('sort_order')->get(),
        );
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
