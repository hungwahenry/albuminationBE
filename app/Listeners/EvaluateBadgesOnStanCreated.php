<?php

namespace App\Listeners;

use App\Events\StanCreated;
use App\Jobs\EvaluateBadgesJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnStanCreated implements ShouldQueue
{
    public function handle(StanCreated $event): void
    {
        EvaluateBadgesJob::dispatch('stan_created', $event->user->id);
    }
}
