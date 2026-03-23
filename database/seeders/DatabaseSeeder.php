<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AdminRolesSeeder;
use Database\Seeders\AppConfigSeeder;
use Database\Seeders\FeedSectionSeeder;
use Database\Seeders\NotificationTypeSeeder;
use Database\Seeders\ReportReasonSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminRolesSeeder::class,
            ReportReasonSeeder::class,
            AppConfigSeeder::class,
            FeedSectionSeeder::class,
            NotificationTypeSeeder::class,
        ]);
    }
}
