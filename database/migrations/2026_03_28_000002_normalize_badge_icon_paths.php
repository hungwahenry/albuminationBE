<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('badges')
            ->where('icon', 'like', 'https://api.albumination.com/storage/%')
            ->get(['id', 'icon'])
            ->each(fn ($badge) => DB::table('badges')->where('id', $badge->id)->update([
                'icon' => substr($badge->icon, strlen('https://api.albumination.com/storage/')),
            ]));
    }

    public function down(): void {}
};
