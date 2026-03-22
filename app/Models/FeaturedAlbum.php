<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedAlbum extends Model
{
    protected $fillable = [
        'album_id',
        'sort_order',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
