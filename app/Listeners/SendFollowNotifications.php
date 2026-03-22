<?php

namespace App\Listeners;

use App\Events\FollowCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFollowNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(FollowCreated $event): void
    {
        $this->notifications->onFollowCreated($event->follower, $event->target);
    }
}

