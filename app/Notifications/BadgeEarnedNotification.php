<?php

namespace App\Notifications;

use App\Models\Badge;
use App\Notifications\Channels\ExpoPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BadgeEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Badge $badge) {}

    public function via(object $notifiable): array
    {
        return ['database', ExpoPushChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'              => 'badge_earned',
            'badge_slug'        => $this->badge->slug,
            'badge_name'        => $this->badge->name,
            'badge_description' => $this->badge->description,
            'badge_icon'        => $this->badge->icon,
            'badge_rarity'      => $this->badge->rarity,
            'title'             => 'Badge unlocked',
            'message'           => "You earned \"{$this->badge->name}\"",
        ];
    }
}
