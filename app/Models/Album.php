<?php

namespace App\Models;

use App\Traits\Loveable;
use App\Services\CoverArtService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Album extends Model
{
    use HasSlug, Loveable, Searchable;

    protected $fillable = [
        'mbid',
        'slug',
        'title',
        'type',
        'release_date',
        'cover_art_url',
        'loves_count',
        'takes_count',
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
            'release_date' => 'date',
            'loves_count'  => 'integer',
            'takes_count'  => 'integer',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->withPivot(['join_phrase', 'order'])
            ->orderByPivot('order');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('position');
    }

    public function takes(): HasMany
    {
        return $this->hasMany(Take::class);
    }

    public function getCoverArtUrlAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        return CoverArtService::url($this->mbid);
    }
}
