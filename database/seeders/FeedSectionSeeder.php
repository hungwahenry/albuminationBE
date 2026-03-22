<?php

namespace Database\Seeders;

use App\Models\FeedSection;
use Illuminate\Database\Seeder;

class FeedSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'type'             => 'featured_albums',
                'title'            => 'Featured Albums',
                'subtitle'         => null,
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 0,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'new_to_albumination',
                'title'            => 'New to Albumination',
                'subtitle'         => 'Fresh additions to the catalogue',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 1,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'trending_albums',
                'title'            => 'Trending Albums',
                'subtitle'         => 'Most loved this month',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20, 'days' => 30],
                'is_active'        => true,
                'sort_order'       => 2,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'friends_rotations',
                'title'            => 'Latest from Friends',
                'subtitle'         => 'Rotations from people you follow',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 3,
                'requires_follows' => true,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'friends_takes',
                'title'            => 'Takes from Friends',
                'subtitle'         => 'What your people are saying',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 4,
                'requires_follows' => true,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'popular_rotations',
                'title'            => 'Popular Rotations',
                'subtitle'         => 'Most loved this week',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20, 'days' => 7],
                'is_active'        => true,
                'sort_order'       => 5,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'latest_rotations',
                'title'            => 'Latest Rotations',
                'subtitle'         => 'Fresh from the community',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 6,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'top_takes',
                'title'            => 'Top Takes',
                'subtitle'         => 'Most agreed opinions this week',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20, 'days' => 7],
                'is_active'        => true,
                'sort_order'       => 7,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'latest_takes',
                'title'            => 'Latest Takes',
                'subtitle'         => 'Hot off the press',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 8,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'top_albums',
                'title'            => 'Top Albums',
                'subtitle'         => 'All-time most loved on Albumination',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 9,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'rotations_by_vibe',
                'title'            => 'Chill Rotations',
                'subtitle'         => null,
                'config'           => ['carousel_limit' => 8, 'per_page' => 20, 'vibetag_id' => null],
                'is_active'        => false,
                'sort_order'       => 10,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
            [
                'type'             => 'suggested_users',
                'title'            => 'People to Follow',
                'subtitle'         => 'Discover the community',
                'config'           => ['carousel_limit' => 8, 'per_page' => 20],
                'is_active'        => true,
                'sort_order'       => 11,
                'requires_follows' => false,
                'min_account_age_days' => null,
            ],
        ];

        foreach ($sections as $data) {
            FeedSection::updateOrCreate(
                ['type' => $data['type']],
                $data,
            );
        }
    }
}
