<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when a field on the subject satisfies an operator/value comparison.
 *
 * Criteria: { "type": "attribute", "field": "rating", "operator": "=", "value": 5 }
 */
class AttributeEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        if ($subject === null) return false;

        $actual   = $subject->getAttribute($this->criteria['field']);
        $expected = $this->criteria['value'];

        return match ($this->criteria['operator'] ?? '=') {
            '='  => $actual == $expected,
            '!=' => $actual != $expected,
            '>'  => $actual >  $expected,
            '>=' => $actual >= $expected,
            '<'  => $actual <  $expected,
            '<=' => $actual <= $expected,
            default => false,
        };
    }
}
