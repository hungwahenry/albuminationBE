<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_track', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->string('join_phrase')->nullable();
            $table->unsignedSmallInteger('order')->default(0);

            $table->unique(['artist_id', 'track_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_track');
    }
};
