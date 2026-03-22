<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->index('loves_count');
            $table->index('created_at');
        });

        Schema::table('rotations', function (Blueprint $table) {
            $table->index(['status', 'published_at', 'loves_count']);
        });

        Schema::table('takes', function (Blueprint $table) {
            $table->index(['is_deleted', 'created_at', 'agrees_count']);
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropIndex(['loves_count']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('rotations', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at', 'loves_count']);
        });

        Schema::table('takes', function (Blueprint $table) {
            $table->dropIndex(['is_deleted', 'created_at', 'agrees_count']);
        });
    }
};
