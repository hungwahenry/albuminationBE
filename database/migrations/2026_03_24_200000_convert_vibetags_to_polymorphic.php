<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('vibetag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');

            $table->unique(['vibetag_id', 'taggable_type', 'taggable_id']);
        });

        // Migrate existing rotation_vibetag data
        if (Schema::hasTable('rotation_vibetag')) {
            \DB::statement('
                INSERT INTO taggables (vibetag_id, taggable_type, taggable_id)
                SELECT vibetag_id, \'App\\\\Models\\\\Rotation\', rotation_id
                FROM rotation_vibetag
            ');

            Schema::drop('rotation_vibetag');
        }
    }

    public function down(): void
    {
        Schema::create('rotation_vibetag', function (Blueprint $table) {
            $table->foreignId('rotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vibetag_id')->constrained()->cascadeOnDelete();
            $table->unique(['rotation_id', 'vibetag_id']);
        });

        \DB::statement('
            INSERT INTO rotation_vibetag (rotation_id, vibetag_id)
            SELECT taggable_id, vibetag_id
            FROM taggables
            WHERE taggable_type = \'App\\\\Models\\\\Rotation\'
        ');

        Schema::drop('taggables');
    }
};
