<?php

namespace App\Services\Moderation;

class ModerationResult
{
    /**
     * @param array<string, float> $scores raw OpenAI category_scores (empty when allowed without API call)
     */
    private function __construct(
        public readonly bool $allowed,
        public readonly ?string $flaggedCategory = null,
        public readonly ?float $flaggedScore = null,
        public readonly array $scores = [],
        public readonly bool $fromCache = false,
        public readonly bool $apiSkipped = false,
    ) {}

    public static function allowed(array $scores = [], bool $fromCache = false): self
    {
        return new self(allowed: true, scores: $scores, fromCache: $fromCache);
    }

    public static function blocked(string $category, float $score, array $scores, bool $fromCache = false): self
    {
        return new self(
            allowed: false,
            flaggedCategory: $category,
            flaggedScore: $score,
            scores: $scores,
            fromCache: $fromCache,
        );
    }

    public static function skipped(): self
    {
        return new self(allowed: true, apiSkipped: true);
    }
}
