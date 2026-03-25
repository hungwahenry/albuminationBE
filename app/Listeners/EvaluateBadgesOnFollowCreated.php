<?php

namespace App\Listeners;

use App\Events\FollowCreated;
use App\Jobs\EvaluateBadgesJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnFollowCreated implements ShouldQueue
{
    public function handle(FollowCreated $event): void
    {
        // Badges for the person who followed (following count)
        EvaluateBadgesJob::dispatch('follow_given', $event->follower->id);

        // Badges for the person who received the follow (follower count)
        EvaluateBadgesJob::dispatch('follow_received', $event->target->id);
    }
}
