<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->unsignedInteger('favourites_count')->default(0)->after('position');
        });

        // Backfill existing counts
        DB::statement('
            UPDATE tracks
            SET favourites_count = (
                SELECT COUNT(*) FROM track_favourites WHERE track_favourites.track_id = tracks.id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn('favourites_count');
        });
    }
};
