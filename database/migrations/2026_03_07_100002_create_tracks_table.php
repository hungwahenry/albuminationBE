<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->uuid('mbid')->unique(); // recording MBID
            $table->string('title');
            $table->unsignedInteger('length')->nullable(); // duration in milliseconds
            $table->unsignedSmallInteger('position')->nullable(); // track number on album
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
