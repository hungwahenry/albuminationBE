<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notifications')
            ->where('type', 'App\Notifications\BadgeEarnedNotification')
            ->get()
            ->each(function ($notification) {
                $data = json_decode($notification->data, true);

                if (!isset($data['type']) || $data['type'] !== 'badge_earned') return;
                if (isset($data['badge'])) return; // already migrated

                $icon = $data['badge_icon'] ?? null;
                $iconUrl = $icon
                    ? (str_starts_with($icon, 'http') ? $icon : Storage::disk('public')->url($icon))
                    : null;

                $rarityConfig = ($data['badge_rarity_color'] ?? null) ? [
                    'key'            => $data['badge_rarity'],
                    'label'          => $data['badge_rarity_label'] ?? '',
                    'color'          => $data['badge_rarity_color'],
                    'bg_color'       => $data['badge_rarity_bg_color'] ?? '',
                    'bg_light_color' => $data['badge_rarity_bg_light'] ?? '',
                ] : null;

                $newData = [
                    'type'    => 'badge_earned',
                    'title'   => $data['title'] ?? 'Badge unlocked',
                    'message' => $data['message'] ?? '',
                    'badge'   => [
                        'slug'         => $data['badge_slug'] ?? '',
                        'name'         => $data['badge_name'] ?? '',
                        'description'  => $data['badge_description'] ?? '',
                        'icon_url'     => $iconUrl,
                        'rarity'       => $data['badge_rarity'] ?? 'common',
                        'rarity_config' => $rarityConfig,
                        'earned_at'    => $notification->created_at,
                    ],
                ];

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['data' => json_encode($newData)]);
            });
    }

    public function down(): void {}
};
