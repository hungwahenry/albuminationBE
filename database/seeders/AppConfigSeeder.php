<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Maintenance
            ['key' => 'maintenance_mode',    'value' => 'false', 'type' => 'boolean', 'group' => 'maintenance', 'description' => 'Take the entire API offline. All authenticated requests return a 503 with the maintenance message.'],
            ['key' => 'maintenance_message', 'value' => "Albumination is undergoing scheduled maintenance. We'll be back shortly.", 'type' => 'string', 'group' => 'maintenance', 'description' => 'Message returned to the app during maintenance mode.'],

            // Registration
            ['key' => 'registration_open', 'value' => 'true', 'type' => 'boolean', 'group' => 'registration', 'description' => 'Allow new users to sign up via the magic code flow. Disabling blocks new signup codes from being issued.'],

            // Feature kill switches
            ['key' => 'takes_enabled',     'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to create new takes.'],
            ['key' => 'rotations_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to create new rotations.'],
            ['key' => 'comments_enabled',  'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to post new rotation comments.'],
            ['key' => 'replies_enabled',   'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to reply to takes.'],
            ['key' => 'reactions_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to react to takes (loves, hits, misses).'],
            ['key' => 'follows_enabled',   'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to follow other users.'],
            ['key' => 'reports_enabled',   'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow users to submit reports on content or other users.'],
            ['key' => 'search_enabled',    'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Enable the search endpoint for albums, artists, tracks, and users.'],
            ['key' => 'mb_search_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'features', 'description' => 'Allow MusicBrainz external lookups during search. Disabling falls back to local catalog only.'],

            // Version gates
            ['key' => 'min_ios_version',      'value' => '1.0.0', 'type' => 'string', 'group' => 'versions', 'description' => 'Minimum supported iOS app version (semver). Requests below this receive a force-update response.'],
            ['key' => 'min_android_version',  'value' => '1.0.0', 'type' => 'string', 'group' => 'versions', 'description' => 'Minimum supported Android app version (semver).'],
            ['key' => 'force_update_message', 'value' => 'A new version of Albumination is required. Please update the app to continue.', 'type' => 'string', 'group' => 'versions', 'description' => 'Message shown when the app version is below the minimum.'],

            // Limits
            ['key' => 'max_vibetags_per_rotation', 'value' => '10', 'type' => 'integer', 'group' => 'limits', 'description' => 'Maximum number of vibetags allowed on a single rotation.'],
            ['key' => 'max_rotation_items',        'value' => '50', 'type' => 'integer', 'group' => 'limits', 'description' => 'Maximum number of items (albums/tracks) allowed in a single rotation.'],
        ];

        foreach ($configs as $config) {
            AppConfig::updateOrCreate(['key' => $config['key']], $config);
        }
    }
}
