<?php

namespace Database\Seeders;

use App\Models\ReportReason;
use Illuminate\Database\Seeder;

class ReportReasonSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            // Global reasons (apply to all reportable types)
            ['reportable_type' => null, 'label' => 'Spam',                'description' => 'Unsolicited or repetitive content',     'sort_order' => 1],
            ['reportable_type' => null, 'label' => 'Harassment',          'description' => 'Bullying or targeted harassment',        'sort_order' => 2],
            ['reportable_type' => null, 'label' => 'Hate Speech',         'description' => 'Promotes hatred against a group',        'sort_order' => 3],
            ['reportable_type' => null, 'label' => 'Inappropriate Content', 'description' => 'Sexual, violent, or disturbing content', 'sort_order' => 4],

            // Rotation-specific
            ['reportable_type' => 'App\\Models\\Rotation', 'label' => 'Misleading Title',     'description' => 'Title does not match the rotation content', 'sort_order' => 10],
            ['reportable_type' => 'App\\Models\\Rotation', 'label' => 'Stolen/Copied Content', 'description' => 'This rotation was copied from someone else',  'sort_order' => 11],

            // Take-specific
            ['reportable_type' => 'App\\Models\\Take', 'label' => 'Spoilers',          'description' => 'Contains major untagged spoilers',      'sort_order' => 10],
            ['reportable_type' => 'App\\Models\\Take', 'label' => 'Off-Topic',          'description' => 'Not relevant to the album',              'sort_order' => 11],

            // User-specific
            ['reportable_type' => 'App\\Models\\User', 'label' => 'Impersonation',      'description' => 'Pretending to be someone else',          'sort_order' => 10],
            ['reportable_type' => 'App\\Models\\User', 'label' => 'Inappropriate Profile', 'description' => 'Offensive username, avatar, or bio', 'sort_order' => 11],
        ];

        foreach ($reasons as $reason) {
            ReportReason::updateOrCreate(
                ['reportable_type' => $reason['reportable_type'], 'label' => $reason['label']],
                $reason,
            );
        }
    }
}
