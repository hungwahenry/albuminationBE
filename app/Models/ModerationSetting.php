<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ModerationSetting extends Model
{
    private const CACHE_KEY = 'moderation_settings:singleton';

    public const FAIL_OPEN   = 'fail_open';
    public const FAIL_CLOSED = 'fail_closed';

    protected $fillable = [
        'enabled',
        'fail_mode',
        'cache_ttl_hours',
    ];

    protected $casts = [
        'enabled'         => 'boolean',
        'cache_ttl_hours' => 'integer',
    ];

    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => self::firstOrFail());
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
