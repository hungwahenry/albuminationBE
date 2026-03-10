<?php

namespace App\Policies;

use App\Models\RotationComment;
use App\Models\User;

class RotationCommentPolicy
{
    public function delete(User $user, RotationComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
