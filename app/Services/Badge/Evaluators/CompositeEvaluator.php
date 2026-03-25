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
        foreach ($this->criteria['criteria'] as $nested) {
            if (!EvaluatorFactory::make($nested)->passes($user, $subject)) {
                return false;
            }
        }

        return true;
    }
}
