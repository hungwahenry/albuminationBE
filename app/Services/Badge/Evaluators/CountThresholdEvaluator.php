<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use App\Services\Badge\ActionRegistry;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when the user's count of a relation meets or exceeds a threshold.
 *
 * Via Eloquent relation (with optional where scope):
 *   { "type": "count_threshold", "user_relation": "takes", "threshold": 10 }
 *   { "type": "count_threshold", "user_relation": "rotations", "where": {"status": "published"}, "threshold": 5 }
 *
 * Via registered action (see ActionRegistry):
 *   { "type": "count_threshold", "action": "loves_given", "threshold": 50 }
 */
class CountThresholdEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        return $this->resolve($user) >= (int) $this->criteria['threshold'];
    }

    private function resolve(User $user): int
    {
        if (isset($this->criteria['action'])) {
            return ActionRegistry::resolve($this->criteria['action'], $user);
        }

        $query = $user->{$this->criteria['user_relation']}();

        foreach ($this->criteria['where'] ?? [] as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }
}
