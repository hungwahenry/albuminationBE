<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Notification records stored when badge icons were still name strings
     * ('fire', 'pen', etc.) will have a broken icon_url like:
     *   http://example.com/storage/fire
     *
     * For each badge_earned notification, look up the badge by slug to get the
     * current icon path (a real uploaded file or null), then rebuild icon_url
     * correctly. This brings stored notifications in line with the current state
     * of the badges table.
     */
    public function up(): void
    {
        $badgeIconMap = DB::table('badges')
            ->whereNotNull('icon')
            ->where('icon', 'like', 'badges/%')
            ->pluck('icon', 'slug');

        DB::table('notifications')
            ->where('type', 'App\Notifications\BadgeEarnedNotification')
            ->orderBy('id')
            ->chunk(200, function ($notifications) use ($badgeIconMap) {
                foreach ($notifications as $notification) {
                    $data = json_decode($notification->data, true);

                    if (($data['type'] ?? null) !== 'badge_earned') continue;
                    if (!isset($data['badge']['slug'])) continue;

                    $slug        = $data['badge']['slug'];
                    $currentIcon = $badgeIconMap[$slug] ?? null;
                    $correctUrl  = $currentIcon ? Storage::disk('public')->url($currentIcon) : null;

                    $existingUrl = $data['badge']['icon_url'] ?? null;

                    // Skip if already correct
                    if ($existingUrl === $correctUrl) continue;

                    $data['badge']['icon_url'] = $correctUrl;

                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['data' => json_encode($data)]);
                }
            });
    }

    public function down(): void {}
};
