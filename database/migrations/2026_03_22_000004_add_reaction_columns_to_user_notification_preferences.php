<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->boolean('reaction_in_app')->default(true)->after('like_content_mail');
            $table->boolean('reaction_push')->default(true)->after('reaction_in_app');
            $table->boolean('reaction_mail')->default(false)->after('reaction_push');
        });
    }

    public function down(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->dropColumn(['reaction_in_app', 'reaction_push', 'reaction_mail']);
        });
    }
};
