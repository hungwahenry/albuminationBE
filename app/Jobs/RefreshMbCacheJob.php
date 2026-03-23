<?php

namespace App\Jobs;

use App\Services\SearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshMbCacheJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;
    public array $backoff = [10, 30];

    public function __construct(
        public readonly string $type,
        public readonly string $query,
        public readonly int $limit,
    ) {}

    public function handle(SearchService $searchService): void
    {
        $searchService->warmMbCache($this->type, $this->query, $this->limit);
    }
}
