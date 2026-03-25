<?php

namespace App\Listeners;

use App\Events\TakeReactionChanged;
use App\Jobs\EvaluateBadgesJob;
use App\Models\Take;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnTakeReactionChanged implements ShouldQueue
{
    public function handle(TakeReactionChanged $event): void
    {
        // Evaluate badges for the take owner based on their take's reaction counts
        EvaluateBadgesJob::dispatch(
            'take_reacted',
            $event->take->user_id,
            Take::class,
            $event->take->id,
        );
    }
}
