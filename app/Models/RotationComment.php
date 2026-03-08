<?php

namespace App\Models;

use App\Traits\Loveable;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RotationComment extends Model
{
    use Loveable, Reportable;

    protected $fillable = [
        'user_id',
        'rotation_id',
        'parent_id',
        'reply_to_user_id',
        'body',
        'gif_url',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted'     => 'boolean',
            'replies_count'  => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function replyToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_to_user_id');
    }

    public function isTopLevel(): bool
    {
        return $this->parent_id === null;
    }
}
