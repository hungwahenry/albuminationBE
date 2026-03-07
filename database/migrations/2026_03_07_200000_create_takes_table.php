<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('takes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->enum('rating', ['hit', 'miss']);
            $table->text('body');
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->unsignedBigInteger('agrees_count')->default(0);
            $table->unsignedBigInteger('disagrees_count')->default(0);
            $table->unsignedBigInteger('replies_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'album_id']); // one take per user per album
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('takes');
    }
};
