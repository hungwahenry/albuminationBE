<?php

namespace Database\Seeders;

use App\Models\BadgeRarityConfig;
use Illuminate\Database\Seeder;

class BadgeRaritySeeder extends Seeder
{
    public function run(): void
    {
        $rarities = [
            [
                'key'           => 'common',
                'label'         => 'Common',
                'color'         => '#71717a',
                'bg_color'      => '#71717a',
                'bg_light_color'=> '#f4f4f5',
                'sort_order'    => 1,
            ],
            [
                'key'           => 'rare',
                'label'         => 'Rare',
                'color'         => '#3b82f6',
                'bg_color'      => '#3b82f6',
                'bg_light_color'=> '#eff6ff',
                'sort_order'    => 2,
            ],
            [
                'key'           => 'epic',
                'label'         => 'Epic',
                'color'         => '#a855f7',
                'bg_color'      => '#a855f7',
                'bg_light_color'=> '#faf5ff',
                'sort_order'    => 3,
            ],
            [
                'key'           => 'legendary',
                'label'         => 'Legendary',
                'color'         => '#f59e0b',
                'bg_color'      => '#f59e0b',
                'bg_light_color'=> '#fffbeb',
                'sort_order'    => 4,
            ],
        ];

        foreach ($rarities as $rarity) {
            BadgeRarityConfig::updateOrCreate(['key' => $rarity['key']], $rarity);
        }
    }
}
