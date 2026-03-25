<?php

namespace App\Observers;

use App\Jobs\EvaluateBadgesJob;
use App\Models\Profile;

class ProfileObserver
{
    public function updated(Profile $profile): void
    {
        EvaluateBadgesJob::dispatch('profile_updated', $profile->user_id, Profile::class, $profile->id);
    }
}
