<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // New follower
            $table->boolean('new_follower_in_app')->default(true);
            $table->boolean('new_follower_push')->default(true);
            $table->boolean('new_follower_mail')->default(false);

            // Likes / loves
            $table->boolean('like_content_in_app')->default(true);
            $table->boolean('like_content_push')->default(true);
            $table->boolean('like_content_mail')->default(false);

            // Comments
            $table->boolean('comment_content_in_app')->default(true);
            $table->boolean('comment_content_push')->default(true);
            $table->boolean('comment_content_mail')->default(false);

            // Replies
            $table->boolean('reply_content_in_app')->default(true);
            $table->boolean('reply_content_push')->default(true);
            $table->boolean('reply_content_mail')->default(false);

            // Rotations
            $table->boolean('rotation_published_in_app')->default(true);
            $table->boolean('rotation_published_push')->default(true);
            $table->boolean('rotation_published_mail')->default(false);

            // Reports / moderation
            $table->boolean('report_updates_in_app')->default(true);
            $table->boolean('report_updates_push')->default(false);
            $table->boolean('report_updates_mail')->default(true);

            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};

