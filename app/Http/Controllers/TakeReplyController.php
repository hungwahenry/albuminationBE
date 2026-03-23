<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTakeReplyRequest;
use App\Http\Resources\TakeReplyResource;
use App\Models\Profile;
use App\Models\Take;
use App\Models\TakeReply;
use App\Services\TakeReplyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TakeReplyController extends Controller
{
    use ApiResponse;

    public function __construct(private TakeReplyService $replyService) {}

    public function index(Request $request, string $slug, Take $take): JsonResponse
    {
        $replies = $take->replies()
            ->with([
                'user.profile',
                'replyToUser.profile',
                'loves' => fn ($q) => $q->where('user_id', $request->user()->id),
            ])
            ->oldest()
            ->paginate(30);

        return $this->success(TakeReplyResource::collection($replies)->response()->getData(true));
    }

    public function store(StoreTakeReplyRequest $request, string $slug, Take $take): JsonResponse
    {
        $replyToUserId = null;
        if (!empty($request->reply_to_username)) {
            $replyToUserId = Profile::where('username', $request->reply_to_username)->value('user_id');
        }

        $reply = $this->replyService->create($request->user(), $take, $request->body, $request->gif_url, $replyToUserId);

        return $this->success(new TakeReplyResource($reply), 'Reply posted', 201);
    }

    public function destroy(Request $request, string $slug, Take $take, TakeReply $reply): JsonResponse
    {
        if ($reply->take_id !== $take->id) {
            return $this->error('Reply not found', 404);
        }

        Gate::authorize('delete', $reply);

        $this->replyService->delete($reply);

        return $this->success(null, 'Reply deleted');
    }
}
