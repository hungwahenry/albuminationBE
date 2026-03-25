<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TrackFavourite extends Pivot
{
    protected $table = 'track_favourites';

    public $incrementing = true;
    public $timestamps   = false;

    protected $fillable = ['user_id', 'track_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
