<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when the user has exactly 1 of the specified relation.
 *
 * Basic:  { "type": "first", "user_relation": "takes" }
 * Scoped: { "type": "first", "user_relation": "rotations", "where": {"status": "published"} }
 */
class FirstActionEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $query = $user->{$this->criteria['user_relation']}();

        foreach ($this->criteria['where'] ?? [] as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count() === 1;
    }
}
