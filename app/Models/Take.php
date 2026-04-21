<?php

namespace App\Models;

use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Take extends Model
{
    use Reportable;

    protected $fillable = ['user_id', 'album_id', 'rating', 'rating_flipped', 'body', 'is_deleted', 'edited_at'];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
            'edited_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(TakeReaction::class);
    }

    public function agrees(): HasMany
    {
        return $this->hasMany(TakeReaction::class)->where('type', 'agree');
    }

    public function disagrees(): HasMany
    {
        return $this->hasMany(TakeReaction::class)->where('type', 'disagree');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TakeReply::class);
    }

    public function getUserReaction(int $userId): ?string
    {
        if ($this->relationLoaded('reactions')) {
            return $this->reactions->where('user_id', $userId)->first()?->type;
        }

        return $this->reactions()->where('user_id', $userId)->value('type');
    }
}
