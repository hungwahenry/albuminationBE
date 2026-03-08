<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
