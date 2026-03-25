<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description');
            $table->string('icon');
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->string('trigger');           // event name that kicks off evaluation
            $table->json('criteria');            // evaluator config
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('trigger');
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        // Track who first seeded an album into the db
        Schema::table('albums', function (Blueprint $table) {
            $table->foreignId('seeded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'seeded_by_user_id');
            $table->dropColumn('seeded_by_user_id');
        });

        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
