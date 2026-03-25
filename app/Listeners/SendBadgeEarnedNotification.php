<?php

namespace App\Listeners;

use App\Events\BadgeEarned;
use App\Notifications\BadgeEarnedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBadgeEarnedNotification implements ShouldQueue
{
    public function handle(BadgeEarned $event): void
    {
        $event->user->notify(new BadgeEarnedNotification($event->badge));
    }
}
