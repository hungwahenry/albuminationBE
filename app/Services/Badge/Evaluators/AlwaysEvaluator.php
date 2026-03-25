<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Always passes. Used for one-shot badges where the dispatch point
 * already guarantees the condition (e.g. data_exported).
 *
 * Criteria: { "type": "always" }
 */
class AlwaysEvaluator implements BadgeEvaluatorContract
{
    public function passes(User $user, ?Model $subject): bool
    {
        return true;
    }
}
