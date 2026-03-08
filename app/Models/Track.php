<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Track extends Model
{
    use HasSlug, Searchable;

    protected $fillable = [
        'mbid',
        'slug',
        'title',
        'length',
        'position',
        'favourites_count',
        'album_id',
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
            'length' => 'integer',
            'position' => 'integer',
            'favourites_count' => 'integer',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->withPivot(['join_phrase', 'order'])
            ->orderByPivot('order');
    }

    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'track_favourites')
            ->withTimestamps(false);
    }

    public function isFavouritedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->favouritedBy()->where('user_id', $userId)->exists();
    }
}
