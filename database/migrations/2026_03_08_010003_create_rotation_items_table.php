<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('album_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['rotation_id', 'album_id']);
            $table->unique(['rotation_id', 'track_id']);
            $table->index(['rotation_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_items');
    }
};
