<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Services\CoverArtService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CoverController extends Controller
{
    use ApiResponse;

    public function __invoke(): JsonResponse
    {
        $covers = Cache::remember('welcome:covers', 3600, function () {
            return Album::inRandomOrder()
                ->limit(28)
                ->pluck('mbid')
                ->map(fn (string $mbid) => CoverArtService::url($mbid))
                ->all();
        });

        return $this->success($covers);
    }
}
