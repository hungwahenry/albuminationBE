<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('followers_count')->default(0)->after('place_name');
            $table->unsignedBigInteger('following_count')->default(0)->after('followers_count');
            $table->unsignedBigInteger('rotations_count')->default(0)->after('following_count');
            $table->unsignedBigInteger('takes_count')->default(0)->after('rotations_count');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['followers_count', 'following_count', 'rotations_count', 'takes_count']);
        });
    }
};
