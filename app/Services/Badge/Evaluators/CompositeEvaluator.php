<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use App\Services\Badge\EvaluatorFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes only when ALL nested criteria pass.
 *
 * Criteria: { "type": "all", "criteria": [ {...}, {...} ] }
 */
class CompositeEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $nested = $this->criteria['criteria'] ?? array_values($this->criteria['evaluators'] ?? []);

        foreach ($nested as $sub) {
            if (!EvaluatorFactory::make($sub)->passes($user, $subject)) {
                return false;
            }
        }

        return true;
    }
}
