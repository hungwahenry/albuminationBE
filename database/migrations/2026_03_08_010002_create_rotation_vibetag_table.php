<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotation_vibetag', function (Blueprint $table) {
            $table->foreignId('rotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vibetag_id')->constrained()->cascadeOnDelete();

            $table->unique(['rotation_id', 'vibetag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_vibetag');
    }
};
