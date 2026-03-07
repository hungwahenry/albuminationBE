<?php

namespace App\Models;

use App\Services\CoverArtService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Album extends Model
{
    use Searchable;

    protected $fillable = [
        'mbid',
        'title',
        'type',
        'release_date',
        'cover_art_url',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
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

    public function getCoverArtUrlAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        return CoverArtService::url($this->mbid);
    }
}
