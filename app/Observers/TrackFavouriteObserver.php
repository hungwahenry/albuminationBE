<?php

namespace App\Observers;

use App\Jobs\EvaluateBadgesJob;
use App\Models\TrackFavourite;

class TrackFavouriteObserver
{
    public function created(TrackFavourite $favourite): void
    {
        EvaluateBadgesJob::dispatch('track_favourited', $favourite->user_id);
    }
}
