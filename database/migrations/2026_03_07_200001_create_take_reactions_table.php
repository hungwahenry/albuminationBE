<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('take_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('take_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['agree', 'disagree']);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'take_id']); // one reaction per user per take
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('take_reactions');
    }
};
