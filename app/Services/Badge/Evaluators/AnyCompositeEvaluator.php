<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use App\Services\Badge\EvaluatorFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when ANY one of the nested criteria passes (OR logic).
 *
 * Criteria: { "type": "any", "criteria": [ {...}, {...} ] }
 */
class AnyCompositeEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $nested = $this->criteria['criteria'] ?? [];

        foreach ($nested as $sub) {
            if (EvaluatorFactory::make($sub)->passes($user, $subject)) {
                return true;
            }
        }

        return false;
    }
}
