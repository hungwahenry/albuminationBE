<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Artist extends Model
{
    use Searchable;

    protected $fillable = [
        'mbid',
        'name',
        'sort_name',
        'type',
        'country',
        'disambiguation',
        'begin_date',
        'end_date',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'begin_date' => 'date',
            'end_date' => 'date',
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
}
