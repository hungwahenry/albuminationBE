<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('requires_follows')->default(false);
            $table->unsignedInteger('min_account_age_days')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_sections');
    }
};
