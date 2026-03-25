<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when the user's profile has all key fields filled in.
 *
 * Criteria: { "type": "profile_complete" }
 */
class ProfileCompletenessEvaluator implements BadgeEvaluatorContract
{
    public function passes(User $user, ?Model $subject): bool
    {
        $profile = $user->profile;

        if ($profile === null) return false;

        return filled($profile->display_name)
            && filled($profile->username)
            && filled($profile->bio)
            && filled($profile->avatar)
            && filled($profile->location);
    }
}
