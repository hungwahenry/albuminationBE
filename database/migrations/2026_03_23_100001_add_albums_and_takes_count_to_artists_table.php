<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->unsignedBigInteger('albums_count')->default(0)->after('stans_count');
            $table->unsignedBigInteger('takes_count')->default(0)->after('albums_count');
        });

        DB::statement('
            UPDATE artists
            SET albums_count = (
                SELECT COUNT(*)
                FROM album_artist
                WHERE album_artist.artist_id = artists.id
            )
        ');

        DB::statement('
            UPDATE artists
            SET takes_count = (
                SELECT COUNT(*)
                FROM takes
                INNER JOIN album_artist ON album_artist.album_id = takes.album_id
                WHERE album_artist.artist_id = artists.id
                AND takes.is_deleted = 0
            )
        ');
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn(['albums_count', 'takes_count']);
        });
    }
};
