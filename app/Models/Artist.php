<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Artist extends Model
{
    use HasSlug, Searchable;

    protected $fillable = [
        'mbid',
        'slug',
        'name',
        'sort_name',
        'type',
        'country',
        'disambiguation',
        'begin_date',
        'end_date',
        'image_url',
        'stans_count',
        'albums_count',
        'takes_count',
        'albums_synced_at',
        'views_count',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(200);
    }

    protected function casts(): array
    {
        return [
            'begin_date' => 'date',
            'end_date' => 'date',
            'albums_synced_at' => 'datetime',
            'stans_count'  => 'integer',
            'albums_count' => 'integer',
            'takes_count'  => 'integer',
            'views_count'  => 'integer',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'disambiguation' => $this->disambiguation,
        ];
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class)
            ->withPivot(['join_phrase', 'order'])
            ->orderByPivot('order');
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class)
            ->withPivot(['join_phrase', 'order'])
            ->orderByPivot('order');
    }

    public function stans(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artist_stans');
    }

    public function isStannedBy(int $userId): bool
    {
        return $this->stans()->where('user_id', $userId)->exists();
    }
}
