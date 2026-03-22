<?php

namespace App\Listeners;

use App\Events\RotationPublished;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendRotationPublishedNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(RotationPublished $event): void
    {
        $this->notifications->onRotationPublished($event->author, $event->rotation);
    }
}

