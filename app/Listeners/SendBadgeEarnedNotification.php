<?php

namespace App\Listeners;

use App\Events\BadgeEarned;
use App\Notifications\BadgeEarnedNotification;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBadgeEarnedNotification implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(BadgeEarned $event): void
    {
        $this->notifications->notifyDirect(
            $event->user,
            new BadgeEarnedNotification($event->badge),
            withPush: true,
        );
    }
}
