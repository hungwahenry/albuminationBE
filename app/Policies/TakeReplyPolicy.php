<?php

namespace App\Policies;

use App\Models\TakeReply;
use App\Models\User;

class TakeReplyPolicy
{
    public function delete(User $user, TakeReply $reply): bool
    {
        return $reply->user_id === $user->id;
    }
}
