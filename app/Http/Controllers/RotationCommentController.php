<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRotationCommentRequest;
use App\Http\Resources\RotationCommentResource;
use App\Models\Profile;
use App\Models\Rotation;
use App\Models\RotationComment;
use App\Services\RotationCommentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RotationCommentController extends Controller
{
    use ApiResponse;

    public function __construct(private RotationCommentService $commentService) {}

    public function index(Request $request, Rotation $rotation): JsonResponse
    {
        $comments = $rotation->comments()
            ->whereNull('parent_id')
            ->with([
                'user.profile',
                'replyToUser.profile',
                'loves' => fn ($q) => $q->where('user_id', $request->user()->id),
            ])
            ->oldest()
            ->paginate(30);

        return $this->success(RotationCommentResource::collection($comments)->response()->getData(true));
    }

    public function replies(Request $request, Rotation $rotation, RotationComment $comment): JsonResponse
    {
        if ($comment->rotation_id !== $rotation->id || !$comment->isTopLevel()) {
            return $this->error('Comment not found', 404);
        }

        $replies = $comment->replies()
            ->with([
                'user.profile',
                'replyToUser.profile',
                'loves' => fn ($q) => $q->where('user_id', $request->user()->id),
            ])
            ->oldest()
            ->paginate(20);

        return $this->success(RotationCommentResource::collection($replies)->response()->getData(true));
    }

    public function store(StoreRotationCommentRequest $request, Rotation $rotation): JsonResponse
    {
        Gate::authorize('comment', $rotation);

        $replyToUserId = null;
        if (!empty($request->reply_to_username)) {
            $replyToUserId = Profile::where('username', $request->reply_to_username)->value('user_id');
        }

        $comment = $this->commentService->create(
            $request->user(),
            $rotation,
            $request->body,
            $request->gif_url,
            $replyToUserId,
            $request->parent_id,
        );

        return $this->success(new RotationCommentResource($comment), 'Comment posted', 201);
    }

    public function destroy(Request $request, Rotation $rotation, RotationComment $comment): JsonResponse
    {
        if ($comment->rotation_id !== $rotation->id) {
            return $this->error('Comment not found', 404);
        }

        Gate::authorize('delete', $comment);

        $this->commentService->delete($comment);

        return $this->success(null, 'Comment deleted');
    }
}
