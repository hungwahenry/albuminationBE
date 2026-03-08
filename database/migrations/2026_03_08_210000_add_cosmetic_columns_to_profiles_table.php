<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('header_album_id')->nullable()->constrained('albums')->nullOnDelete();
            $table->foreignId('pinned_rotation_id')->nullable()->constrained('rotations')->nullOnDelete();
            $table->string('current_vibe_type')->nullable();
            $table->unsignedBigInteger('current_vibe_id')->nullable();

            $table->index(['current_vibe_type', 'current_vibe_id']);
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['header_album_id']);
            $table->dropForeign(['pinned_rotation_id']);
            $table->dropIndex(['current_vibe_type', 'current_vibe_id']);
            $table->dropColumn(['header_album_id', 'pinned_rotation_id', 'current_vibe_type', 'current_vibe_id']);
        });
    }
};
