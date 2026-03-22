<?php

namespace App\Services;

use App\Events\RotationCommentCreated;
use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RotationCommentService
{
    public function create(User $user, Rotation $rotation, ?string $body, ?string $gifUrl, ?int $replyToUserId, ?int $parentId = null): RotationComment
    {
        return DB::transaction(function () use ($user, $rotation, $body, $gifUrl, $replyToUserId, $parentId) {
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

            $comment->load(['user.profile', 'replyToUser.profile', 'rotation.user']);

            $replyToUser = $comment->replyToUser;

            RotationCommentCreated::dispatch($user, $rotation, $replyToUser, $comment);

            return $comment;
        });
    }

    public function delete(RotationComment $comment): void
    {
        DB::transaction(function () use ($comment) {
            $comment->update(['is_deleted' => true]);
            $comment->rotation->decrement('comments_count');

            if ($comment->parent_id) {
                RotationComment::where('id', $comment->parent_id)->decrement('replies_count');
            }
        });
    }
}
