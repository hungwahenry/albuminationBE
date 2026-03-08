<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('type', ['album', 'track']);
            $table->boolean('is_ranked')->default(false);
            $table->boolean('is_public')->default(true);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedBigInteger('items_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotations');
    }
};
