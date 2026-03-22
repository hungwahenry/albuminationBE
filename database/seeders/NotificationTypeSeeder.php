<?php

namespace Database\Seeders;

use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'key'         => 'new_follower',
                'label'       => 'New followers',
                'description' => 'When someone follows you',
                'sort_order'  => 0,
                'is_active'   => true,
            ],
            [
                'key'         => 'like_content',
                'label'       => 'Likes',
                'description' => 'When someone likes your content',
                'sort_order'  => 1,
                'is_active'   => true,
            ],
            [
                'key'         => 'comment_content',
                'label'       => 'Comments',
                'description' => 'When someone comments on your content',
                'sort_order'  => 2,
                'is_active'   => true,
            ],
            [
                'key'         => 'reply_content',
                'label'       => 'Replies',
                'description' => 'When someone replies to your comment',
                'sort_order'  => 3,
                'is_active'   => true,
            ],
            [
                'key'         => 'rotation_published',
                'label'       => 'New rotations',
                'description' => 'From people you follow',
                'sort_order'  => 4,
                'is_active'   => true,
            ],
            [
                'key'         => 'reaction',
                'label'       => 'Reactions',
                'description' => 'When someone agrees or disagrees with your take',
                'sort_order'  => 5,
                'is_active'   => true,
            ],
            [
                'key'         => 'report_updates',
                'label'       => 'Report updates',
                'description' => 'Status updates on reports you submitted',
                'sort_order'  => 6,
                'is_active'   => true,
            ],
        ];

        foreach ($types as $type) {
            NotificationType::updateOrCreate(['key' => $type['key']], $type);
        }
    }
}
