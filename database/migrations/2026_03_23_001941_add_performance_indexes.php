<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('takes', function (Blueprint $table) {
            $table->index('album_id');
        });

        Schema::table('magic_codes', function (Blueprint $table) {
            $table->index(['email', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('takes', function (Blueprint $table) {
            $table->dropIndex(['album_id']);
        });

        Schema::table('magic_codes', function (Blueprint $table) {
            $table->dropIndex(['email', 'type']);
        });
    }
};
