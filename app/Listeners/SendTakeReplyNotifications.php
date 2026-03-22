<?php

namespace App\Listeners;

use App\Events\TakeReplyCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTakeReplyNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(TakeReplyCreated $event): void
    {
        $this->notifications->onTakeReplyCreated(
            replier: $event->replier,
            take: $event->take,
            replyToUser: $event->replyToUser,
            reply: $event->reply,
        );
    }
}

