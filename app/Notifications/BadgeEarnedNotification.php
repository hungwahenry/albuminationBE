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
            'type'    => 'badge_earned',
            'title'   => 'Badge unlocked',
            'message' => "You earned \"{$this->badge->name}\"",
            'badge'   => [
                'slug'         => $this->badge->slug,
                'name'         => $this->badge->name,
                'description'  => $this->badge->description,
                'icon_url'     => $this->badge->icon ? Storage::disk('public')->url($this->badge->icon) : null,
                'rarity'       => $this->badge->rarity,
                'rarity_config' => $rarityConfig ? [
                    'key'            => $rarityConfig->key,
                    'label'          => $rarityConfig->label,
                    'color'          => $rarityConfig->color,
                    'bg_color'       => $rarityConfig->bg_color,
                    'bg_light_color' => $rarityConfig->bg_light_color,
                ] : null,
                'earned_at'    => now()->toISOString(),
            ],
        ];
    }
}
