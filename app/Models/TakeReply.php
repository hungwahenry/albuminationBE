<?php

namespace App\Models;

use App\Traits\Loveable;
use App\Traits\Reportable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TakeReply extends Model
{
    use Loveable, Reportable;

    protected $fillable = ['user_id', 'take_id', 'reply_to_user_id', 'body', 'gif_url', 'is_deleted'];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
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

    public function replyToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_to_user_id');
    }
}
