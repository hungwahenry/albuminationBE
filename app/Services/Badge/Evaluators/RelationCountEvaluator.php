<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when a relation count on the subject meets a threshold.
 *
 * Criteria: { "type": "relation_count", "relation": "loves", "threshold": 10 }
 */
class RelationCountEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        if ($subject === null) return false;

        $count = $subject->{$this->criteria['relation']}()->count();
        return $count >= (int) $this->criteria['threshold'];
    }
}
