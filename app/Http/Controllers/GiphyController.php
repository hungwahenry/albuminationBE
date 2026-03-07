<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiphySearchRequest;
use App\Services\GiphyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GiphyController extends Controller
{
    use ApiResponse;

    public function __construct(private GiphyService $giphyService) {}

    public function trending(Request $request): JsonResponse
    {
        $limit  = min((int) $request->query('limit', 24), 50);
        $offset = max((int) $request->query('offset', 0), 0);

        $result = $this->giphyService->trending($limit, $offset);

        return $this->success($result);
    }

    public function search(GiphySearchRequest $request): JsonResponse
    {
        $limit  = min((int) $request->query('limit', 24), 50);
        $offset = max((int) $request->query('offset', 0), 0);

        $result = $this->giphyService->search($request->q, $limit, $offset);

        return $this->success($result);
    }
}
