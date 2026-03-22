<?php

namespace App\Listeners;

use App\Events\TakeReactionChanged;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTakeReactionNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(TakeReactionChanged $event): void
    {
        $this->notifications->onTakeReacted(
            actor: $event->actor,
            take: $event->take,
            type: $event->type,
        );
    }
}

