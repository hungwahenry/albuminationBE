<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->uuid('mbid')->unique();
            $table->string('name');
            $table->string('sort_name')->nullable();
            $table->string('type')->nullable(); // Person, Group, Orchestra, etc.
            $table->string('country', 2)->nullable();
            $table->string('disambiguation')->nullable();
            $table->date('begin_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artists');
    }
};
