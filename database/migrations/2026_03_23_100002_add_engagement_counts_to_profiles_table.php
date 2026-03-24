<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('loves_received_count')->default(0)->after('takes_count');
            $table->unsignedBigInteger('comments_count')->default(0)->after('loves_received_count');
            $table->unsignedBigInteger('stans_count')->default(0)->after('comments_count');
        });

        // Loves received across rotations, rotation comments, and take replies (excluding self-loves)
        DB::statement("
            UPDATE profiles
            SET loves_received_count = (
                SELECT COUNT(*) FROM loves
                WHERE loves.user_id != profiles.user_id
                AND (
                    (loves.loveable_type = 'App\\\\Models\\\\Rotation'
                        AND loves.loveable_id IN (SELECT id FROM rotations WHERE rotations.user_id = profiles.user_id))
                    OR
                    (loves.loveable_type = 'App\\\\Models\\\\RotationComment'
                        AND loves.loveable_id IN (SELECT id FROM rotation_comments WHERE rotation_comments.user_id = profiles.user_id))
                    OR
                    (loves.loveable_type = 'App\\\\Models\\\\TakeReply'
                        AND loves.loveable_id IN (SELECT id FROM take_replies WHERE take_replies.user_id = profiles.user_id))
                )
            )
        ");

        DB::statement('
            UPDATE profiles
            SET comments_count = (
                SELECT COUNT(*) FROM rotation_comments
                WHERE rotation_comments.user_id = profiles.user_id
                AND rotation_comments.is_deleted = 0
            )
        ');

        DB::statement('
            UPDATE profiles
            SET stans_count = (
                SELECT COUNT(*) FROM artist_stans
                WHERE artist_stans.user_id = profiles.user_id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['loves_received_count', 'comments_count', 'stans_count']);
        });
    }
};
