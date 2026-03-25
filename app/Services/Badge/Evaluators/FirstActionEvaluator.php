<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when the user has exactly 1 of the specified relation.
 *
 * Criteria: { "type": "first", "user_relation": "takes" }
 */
class FirstActionEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $relation = $this->criteria['user_relation'];
        return $this->count($user, $relation) === 1;
    }

    private function count(User $user, string $relation): int
    {
        $query = $user->{$relation}();

        if ($relation === 'rotations') {
            $query->where('status', 'published');
        }

        return $query->count();
    }
}
