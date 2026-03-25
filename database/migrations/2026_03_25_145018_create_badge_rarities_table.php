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
        Schema::create('badge_rarities', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // common | rare | epic | legendary
            $table->string('label');                  // Common, Rare, Epic, Legendary
            $table->string('color');                  // Primary hex — text, borders, dots
            $table->string('bg_color');               // Solid background hex — filled circles
            $table->string('bg_light_color');         // Tinted background hex — chips, pills
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_rarities');
    }
};
