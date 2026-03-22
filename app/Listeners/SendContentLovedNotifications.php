<?php

namespace App\Listeners;

use App\Events\ContentLoved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendContentLovedNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(ContentLoved $event): void
    {
        $this->notifications->onContentLoved($event->actor, $event->loveable);
    }
}

