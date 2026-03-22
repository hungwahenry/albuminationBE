<?php

namespace App\Events;

use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RotationCommentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $commenter,
        public readonly Rotation $rotation,
        public readonly ?User $replyToUser,
        public readonly RotationComment $comment,
    ) {
    }
}

