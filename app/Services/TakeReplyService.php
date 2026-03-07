<?php

namespace App\Services;

use App\Models\Take;
use App\Models\TakeReply;
use App\Models\User;

class TakeReplyService
{
    public function create(User $user, Take $take, ?string $body, ?string $gifUrl, ?int $replyToUserId): TakeReply
    {
        $reply = TakeReply::create([
            'user_id'          => $user->id,
            'take_id'          => $take->id,
            'reply_to_user_id' => $replyToUserId,
            'body'             => $body,
            'gif_url'          => $gifUrl,
        ]);

        $take->increment('replies_count');

        return $reply->load(['user.profile', 'replyToUser.profile']);
    }

    /**
     * Soft-delete a reply — body hidden, thread preserved.
     */
    public function delete(TakeReply $reply): void
    {
        $reply->update(['is_deleted' => true]);
        $reply->take->decrement('replies_count');
    }
}
