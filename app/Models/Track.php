<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Track extends Model
{
    use Searchable;

    protected $fillable = [
        'mbid',
        'title',
        'length',
        'position',
        'album_id',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'integer',
            'position' => 'integer',
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
}
