<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->unsignedBigInteger('hits_count')->default(0)->after('takes_count');
            $table->unsignedBigInteger('misses_count')->default(0)->after('hits_count');
        });

        DB::statement("
            UPDATE albums
            SET hits_count = (SELECT COUNT(*) FROM takes WHERE takes.album_id = albums.id AND takes.rating = 'hit' AND takes.is_deleted = 0),
                misses_count = (SELECT COUNT(*) FROM takes WHERE takes.album_id = albums.id AND takes.rating = 'miss' AND takes.is_deleted = 0)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn(['hits_count', 'misses_count']);
        });
    }
};
