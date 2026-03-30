<?php

namespace App\Notifications;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class BadgeEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Badge $badge) {}

    public function via(object $notifiable): array
    {
        return [];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge(['title' => 'Badge unlocked'], $this->payload());
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => 'Badge unlocked',
            'body'  => "You earned \"{$this->badge->name}\"",
            'data'  => $this->payload(),
        ];
    }

    private function payload(): array
    {
        $this->badge->loadMissing('rarityConfig');
        $rarityConfig = $this->badge->rarityConfig;

        return [
            'type'    => 'badge_earned',
            'message' => "You earned \"{$this->badge->name}\"",
            'badge'   => [
                'slug'          => $this->badge->slug,
                'name'          => $this->badge->name,
                'description'   => $this->badge->description,
                'icon_url'      => $this->badge->icon ? Storage::disk('public')->url($this->badge->icon) : null,
                'rarity'        => $this->badge->rarity,
                'rarity_config' => $rarityConfig ? [
                    'key'            => $rarityConfig->key,
                    'label'          => $rarityConfig->label,
                    'color'          => $rarityConfig->color,
                    'bg_color'       => $rarityConfig->bg_color,
                    'bg_light_color' => $rarityConfig->bg_light_color,
                ] : null,
                'earned_at'     => now()->toISOString(),
            ],
        ];
    }

    private function buildBadgePayload(?object $rarityConfig): array
    {
        return [
            'type'    => 'badge_earned',
            'message' => "You earned \"{$this->badge->name}\"",
            'badge'   => [
                'slug'          => $this->badge->slug,
                'name'          => $this->badge->name,
                'description'   => $this->badge->description,
                'icon_url'      => $this->badge->icon ? Storage::disk('public')->url($this->badge->icon) : null,
                'rarity'        => $this->badge->rarity,
                'rarity_config' => $rarityConfig ? [
                    'key'            => $rarityConfig->key,
                    'label'          => $rarityConfig->label,
                    'color'          => $rarityConfig->color,
                    'bg_color'       => $rarityConfig->bg_color,
                    'bg_light_color' => $rarityConfig->bg_light_color,
                ] : null,
                'earned_at'     => now()->toISOString(),
            ],
        ];
    }
}
