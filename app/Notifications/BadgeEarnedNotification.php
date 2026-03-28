<?php

namespace App\Notifications;

use App\Models\Badge;
use App\Models\BadgeRarityConfig;
use App\Notifications\Channels\ExpoPushChannel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        $rarityConfig = Cache::remember("badge_rarity:{$this->badge->rarity}", 3600, fn () =>
            BadgeRarityConfig::where('key', $this->badge->rarity)->first()
        );

        return [
            'type'                      => 'badge_earned',
            'badge_slug'                => $this->badge->slug,
            'badge_name'                => $this->badge->name,
            'badge_description'         => $this->badge->description,
            'badge_icon'                => $this->badge->icon_file
                                            ? Storage::disk('public')->url($this->badge->icon_file)
                                            : null,
            'badge_rarity'              => $this->badge->rarity,
            'badge_rarity_label'        => $rarityConfig?->label,
            'badge_rarity_color'        => $rarityConfig?->color,
            'badge_rarity_bg_color'     => $rarityConfig?->bg_color,
            'badge_rarity_bg_light'     => $rarityConfig?->bg_light_color,
            'title'                     => 'Badge unlocked',
            'message'                   => "You earned \"{$this->badge->name}\"",
        ];
    }
}
