<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    use Searchable;

    protected $fillable = [
        'user_id',
        'username',
        'display_name',
        'avatar',
        'bio',
        'gender',
        'latitude',
        'longitude',
        'place_name',
        'followers_count',
        'following_count',
        'rotations_count',
        'takes_count',
        'loves_received_count',
        'comments_count',
        'stans_count',
        'views_count',
        'header_album_id',
        'pinned_rotation_id',
        'current_vibe_type',
        'current_vibe_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'followers_count' => 'integer',
            'following_count' => 'integer',
            'rotations_count' => 'integer',
            'takes_count' => 'integer',
            'loves_received_count' => 'integer',
            'comments_count' => 'integer',
            'stans_count'  => 'integer',
            'views_count'  => 'integer',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'username' => $this->username,
            'display_name' => $this->display_name,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function headerAlbum(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'header_album_id');
    }

    public function pinnedRotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class, 'pinned_rotation_id');
    }

    public function currentVibe(): MorphTo
    {
        return $this->morphTo('current_vibe');
    }
}
