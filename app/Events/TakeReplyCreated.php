<?php

namespace App\Events;

use App\Models\Take;
use App\Models\TakeReply;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TakeReplyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $replier,
        public readonly Take $take,
        public readonly ?User $replyToUser,
        public readonly TakeReply $reply,
    ) {
    }
}

