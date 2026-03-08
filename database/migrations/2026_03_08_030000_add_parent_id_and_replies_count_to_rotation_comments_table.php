<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rotation_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('rotation_id')->constrained('rotation_comments')->cascadeOnDelete();
            $table->unsignedInteger('replies_count')->default(0)->after('loves_count');
        });
    }

    public function down(): void
    {
        Schema::table('rotation_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('replies_count');
        });
    }
};
