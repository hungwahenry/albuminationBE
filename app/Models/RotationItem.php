<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RotationItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'rotation_id',
        'album_id',
        'track_id',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
