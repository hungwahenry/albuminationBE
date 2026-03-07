<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\AlbumSearchResource;
use App\Http\Resources\ArtistSearchResource;
use App\Http\Resources\TrackSearchResource;
use App\Http\Resources\UserSearchResource;
use App\Services\SearchService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(private SearchService $searchService) {}

    public function __invoke(SearchRequest $request): JsonResponse
    {
        $results = $this->searchService->search(
            query: $request->validated('q'),
            types: $request->validated('types', []),
            limit: $request->integer('limit', 15),
        );

        $resourceMap = [
            'artists' => ArtistSearchResource::class,
            'albums' => AlbumSearchResource::class,
            'tracks' => TrackSearchResource::class,
            'users' => UserSearchResource::class,
        ];

        $formatted = [];

        foreach ($results as $key => $items) {
            $resource = $resourceMap[$key] ?? null;
            $formatted[$key] = $resource
                ? $resource::collection($items)
                : $items;
        }

        return $this->success($formatted)
            ->header('Cache-Control', 'private, max-age=300');
    }
}
