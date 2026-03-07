<?php

namespace App\Http\Controllers;

use App\Http\Requests\TakeReactionRequest;
use App\Models\Take;
use App\Services\TakeReactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TakeReactionController extends Controller
{
    use ApiResponse;

    public function __construct(private TakeReactionService $reactionService) {}

    public function react(TakeReactionRequest $request, string $mbid, Take $take): JsonResponse
    {
        $result = $this->reactionService->react($request->user(), $take, $request->type);

        return $this->success($result);
    }
}
