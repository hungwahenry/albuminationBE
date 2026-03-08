<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rotations', function (Blueprint $table) {
            $table->unsignedInteger('loves_count')->default(0)->after('items_count');
            $table->unsignedInteger('comments_count')->default(0)->after('loves_count');
        });
    }

    public function down(): void
    {
        Schema::table('rotations', function (Blueprint $table) {
            $table->dropColumn(['loves_count', 'comments_count']);
        });
    }
};
