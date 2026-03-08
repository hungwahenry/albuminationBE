<?php

namespace App\Services;

use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\User;

class RotationCommentService
{
    public function create(User $user, Rotation $rotation, ?string $body, ?string $gifUrl, ?int $replyToUserId, ?int $parentId = null): RotationComment
    {
        $comment = RotationComment::create([
            'user_id'          => $user->id,
            'rotation_id'      => $rotation->id,
            'parent_id'        => $parentId,
            'reply_to_user_id' => $replyToUserId,
            'body'             => $body,
            'gif_url'          => $gifUrl,
        ]);

        $rotation->increment('comments_count');

        if ($parentId) {
            RotationComment::where('id', $parentId)->increment('replies_count');
        }

        return $comment->load(['user.profile', 'replyToUser.profile']);
    }

    public function delete(RotationComment $comment): void
    {
        $comment->update(['is_deleted' => true]);
        $comment->rotation->decrement('comments_count');

        if ($comment->parent_id) {
            RotationComment::where('id', $comment->parent_id)->decrement('replies_count');
        }
    }
}
