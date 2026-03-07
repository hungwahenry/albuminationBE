<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TakeReaction extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'take_id', 'type'];

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

    public function take(): BelongsTo
    {
        return $this->belongsTo(Take::class);
    }
}
