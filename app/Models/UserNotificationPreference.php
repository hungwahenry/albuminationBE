<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'new_follower_in_app',
        'new_follower_push',
        'new_follower_mail',
        'like_content_in_app',
        'like_content_push',
        'like_content_mail',
        'comment_content_in_app',
        'comment_content_push',
        'comment_content_mail',
        'reply_content_in_app',
        'reply_content_push',
        'reply_content_mail',
        'rotation_published_in_app',
        'rotation_published_push',
        'rotation_published_mail',
        'report_updates_in_app',
        'report_updates_push',
        'report_updates_mail',
    ];

    protected function casts(): array
    {
        return [
            'new_follower_in_app' => 'boolean',
            'new_follower_push' => 'boolean',
            'new_follower_mail' => 'boolean',
            'like_content_in_app' => 'boolean',
            'like_content_push' => 'boolean',
            'like_content_mail' => 'boolean',
            'comment_content_in_app' => 'boolean',
            'comment_content_push' => 'boolean',
            'comment_content_mail' => 'boolean',
            'reply_content_in_app' => 'boolean',
            'reply_content_push' => 'boolean',
            'reply_content_mail' => 'boolean',
            'rotation_published_in_app' => 'boolean',
            'rotation_published_push' => 'boolean',
            'rotation_published_mail' => 'boolean',
            'report_updates_in_app' => 'boolean',
            'report_updates_push' => 'boolean',
            'report_updates_mail' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

