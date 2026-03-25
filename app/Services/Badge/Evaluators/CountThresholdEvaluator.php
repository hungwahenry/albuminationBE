<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\Love;
use App\Models\RotationComment;
use App\Models\TakeReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Passes when the user's count of a relation meets or exceeds a threshold.
 *
 * Criteria:
 *   { "type": "count_threshold", "user_relation": "takes", "threshold": 10 }
 *   { "type": "count_threshold", "action": "loves_given", "threshold": 50 }
 *   { "type": "count_threshold", "action": "loves_received", "threshold": 50 }
 *   { "type": "count_threshold", "action": "rotation_comments", "threshold": 1 }
 *   { "type": "count_threshold", "action": "take_replies", "threshold": 1 }
 *   { "type": "count_threshold", "action": "unique_vibetags", "threshold": 5 }
 */
class CountThresholdEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $threshold = (int) $this->criteria['threshold'];
        return $this->resolve($user) >= $threshold;
    }

    private function resolve(User $user): int
    {
        if (isset($this->criteria['action'])) {
            return $this->resolveAction($user, $this->criteria['action']);
        }

        $relation = $this->criteria['user_relation'];
        $query = $user->{$relation}();

        if ($relation === 'rotations') {
            $query->where('status', 'published');
        }

        return $query->count();
    }

    private function resolveAction(User $user, string $action): int
    {
        return match ($action) {
            'loves_given'      => Love::where('user_id', $user->id)->count(),
            'loves_received'   => Love::whereHasMorph('loveable', '*', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'rotation_comments' => RotationComment::where('user_id', $user->id)->count(),
            'take_replies'      => TakeReply::where('user_id', $user->id)->count(),
            'unique_vibetags'   => DB::table('taggables')
                ->join('rotations', function ($join) {
                    $join->on('rotations.id', '=', 'taggables.taggable_id')
                         ->where('taggables.taggable_type', \App\Models\Rotation::class);
                })
                ->where('rotations.user_id', $user->id)
                ->distinct()
                ->count('taggables.vibetag_id'),
            default => 0,
        };
    }
}
