<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->unsignedBigInteger('rotations_count')->default(0)->after('misses_count');
        });

        DB::statement('
            UPDATE albums
            SET rotations_count = (
                SELECT COUNT(DISTINCT rotation_id)
                FROM rotation_items
                WHERE rotation_items.album_id = albums.id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('rotations_count');
        });
    }
};
