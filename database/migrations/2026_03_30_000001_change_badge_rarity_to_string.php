<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            // Decouple rarity from a hardcoded enum so badge_rarities is the
            // single source of truth and new tiers can be added without a schema change.
            $table->string('rarity', 50)->default('common')->change();
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common')->change();
        });
    }
};
