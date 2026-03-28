<?php

namespace App\Services\Badge;

use App\Events\BadgeEarned;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BadgeEvaluator
{
    public function evaluate(string $trigger, User $user, ?Model $subject = null): void
    {
        $candidates = Badge::where('trigger', $trigger)
            ->where('active', true)
            ->get();

        $earnedIds = $user->badges()->pluck('badges.id')->flip();

        foreach ($candidates as $badge) {
            if ($earnedIds->has($badge->id)) {
                continue;
            }

            try {
                if (empty($badge->criteria)) continue;

                $evaluator = EvaluatorFactory::make($badge->criteria);
                if ($evaluator->passes($user, $subject)) {
                    $this->award($user, $badge);
                }
            } catch (\Throwable $e) {
                Log::error("Badge evaluation failed [{$badge->slug}]", [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    private function award(User $user, Badge $badge): void
    {
        $user->badges()->attach($badge->id, ['earned_at' => now()]);

        BadgeEarned::dispatch($user, $badge);
    }
}
