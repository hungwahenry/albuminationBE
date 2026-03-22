<?php

namespace App\Listeners;

use App\Events\RotationCommentCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRotationCommentNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(RotationCommentCreated $event): void
    {
        $this->notifications->onRotationCommentCreated(
            commenter: $event->commenter,
            rotation: $event->rotation,
            replyToUser: $event->replyToUser,
            comment: $event->comment,
        );
    }
}

