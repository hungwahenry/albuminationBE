<?php

namespace App\Observers;

use App\Jobs\EvaluateBadgesJob;
use App\Models\Take;

class TakeObserver
{
    public function created(Take $take): void
    {
        EvaluateBadgesJob::dispatch('take_created', $take->user_id, Take::class, $take->id);
    }
}
