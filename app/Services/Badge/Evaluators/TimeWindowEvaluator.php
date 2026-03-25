<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes based on the current day or time of day.
 *
 * Criteria (day of week):  { "type": "time_window", "days": ["Friday"] }
 * Criteria (time range):   { "type": "time_window", "start": "02:00", "end": "04:00" }
 */
class TimeWindowEvaluator implements BadgeEvaluatorContract
{
    public function __construct(private readonly array $criteria) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $now = Carbon::now();

        if (isset($this->criteria['days'])) {
            return in_array($now->format('l'), $this->criteria['days'], true);
        }

        if (isset($this->criteria['start'], $this->criteria['end'])) {
            $start = Carbon::createFromFormat('H:i', $this->criteria['start']);
            $end   = Carbon::createFromFormat('H:i', $this->criteria['end']);
            return $now->between($start, $end);
        }

        return false;
    }
}
