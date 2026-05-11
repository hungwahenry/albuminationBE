<?php

namespace App\Services;

use App\Models\ModerationCategory;
use App\Models\ModerationSetting;
use App\Services\Moderation\ModerationResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModerationService
{
    private const MAX_INPUT_LENGTH = 32_000;
    private const HTTP_TIMEOUT_SECONDS = 8;

    public function check(string $text): ModerationResult
    {
        $text = trim($text);
        if ($text === '') {
            return ModerationResult::skipped();
        }

        $settings = ModerationSetting::current();

        if (!$settings->enabled) {
            return ModerationResult::skipped();
        }

        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return $this->handleApiFailure($settings, 'OPENAI_API_KEY is not configured');
        }

        if (mb_strlen($text) > self::MAX_INPUT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_INPUT_LENGTH);
        }

        $hash      = hash('sha256', $text);
        $cacheKey  = "moderation:verdict:{$hash}";
        $cacheTtl  = $settings->cache_ttl_hours * 3600;

        if ($cached = Cache::get($cacheKey)) {
            return $this->evaluate($cached, fromCache: true);
        }

        $scores = $this->callOpenAI($text, $apiKey);
        if ($scores === null) {
            return $this->handleApiFailure($settings, 'OpenAI moderation request failed');
        }

        Cache::put($cacheKey, $scores, $cacheTtl);

        return $this->evaluate($scores, fromCache: false);
    }

    /**
     * @return array<string, float>|null  null on failure
     */
    private function callOpenAI(string $text, string $apiKey): ?array
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post('https://api.openai.com/v1/moderations', [
                    'model' => config('services.openai.moderation_model', 'omni-moderation-latest'),
                    'input' => $text,
                ]);

            if (!$response->successful()) {
                Log::warning('OpenAI moderation non-2xx response', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
                return null;
            }

            $payload = $response->json();
            $scores  = $payload['results'][0]['category_scores'] ?? null;

            if (!is_array($scores)) {
                Log::warning('OpenAI moderation response missing category_scores', ['payload' => $payload]);
                return null;
            }

            return array_map('floatval', $scores);
        } catch (\Throwable $e) {
            Log::warning('OpenAI moderation threw', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * @param array<string, float> $scores
     */
    private function evaluate(array $scores, bool $fromCache): ModerationResult
    {
        foreach (ModerationCategory::active() as $category) {
            $score = $scores[$category->name] ?? null;
            if ($score === null) {
                continue;
            }
            if ($score >= $category->threshold) {
                return ModerationResult::blocked(
                    category: $category->name,
                    score:    $score,
                    scores:   $scores,
                    fromCache: $fromCache,
                );
            }
        }

        return ModerationResult::allowed($scores, fromCache: $fromCache);
    }

    private function handleApiFailure(ModerationSetting $settings, string $reason): ModerationResult
    {
        Log::warning('Content moderation falling back to fail mode', [
            'fail_mode' => $settings->fail_mode,
            'reason'    => $reason,
        ]);

        return $settings->fail_mode === ModerationSetting::FAIL_CLOSED
            ? ModerationResult::blocked('api_unavailable', 1.0, [])
            : ModerationResult::allowed();
    }
}
