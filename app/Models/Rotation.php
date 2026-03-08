<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Rotation extends Model
{
    use HasSlug;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'caption',
        'cover_image',
        'type',
        'is_ranked',
        'is_public',
        'status',
        'items_count',
        'published_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(200);
    }

    protected function casts(): array
    {
        return [
            'is_ranked' => 'boolean',
            'is_public' => 'boolean',
            'items_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vibetags(): BelongsToMany
    {
        return $this->belongsToMany(Vibetag::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RotationItem::class)->orderBy('position');
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
