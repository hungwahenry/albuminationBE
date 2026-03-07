<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Love extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'loveable_type', 'loveable_id'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loveable(): MorphTo
    {
        return $this->morphTo();
    }
}
