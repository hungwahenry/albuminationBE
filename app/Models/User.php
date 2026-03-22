<?php

namespace App\Models;

use App\Traits\Reportable;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Reportable;

    protected $fillable = [
        'email',
        'email_verified_at',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function favouriteTracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_favourites');
    }

    public function rotations(): HasMany
    {
        return $this->hasMany(Rotation::class);
    }

    public function takes(): HasMany
    {
        return $this->hasMany(Take::class);
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function isFollowing(int $userId): bool
    {
        if ($this->relationLoaded('following')) {
            return $this->following->where('following_id', $userId)->isNotEmpty();
        }

        return $this->following()->where('following_id', $userId)->exists();
    }

    public function isFollowedBy(int $userId): bool
    {
        if ($this->relationLoaded('followers')) {
            return $this->followers->where('follower_id', $userId)->isNotEmpty();
        }

        return $this->followers()->where('follower_id', $userId)->exists();
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'user_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function stannedArtists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_stans');
    }

    public function reportsAgainst(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function blockedBy(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_user_id');
    }

    public function hasBlocked(int $userId): bool
    {
        if ($this->relationLoaded('blocks')) {
            return $this->blocks->where('blocked_user_id', $userId)->isNotEmpty();
        }

        return $this->blocks()->where('blocked_user_id', $userId)->exists();
    }

    public function isBlockedBy(int $userId): bool
    {
        if ($this->relationLoaded('blockedBy')) {
            return $this->blockedBy->where('user_id', $userId)->isNotEmpty();
        }

        return $this->blockedBy()->where('user_id', $userId)->exists();
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }
}
