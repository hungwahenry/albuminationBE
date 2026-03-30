<?php

namespace App\Services\Badge\Evaluators;

use App\Contracts\BadgeEvaluatorContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Passes when the user's profile has all required fields filled in.
 *
 * Default fields: { "type": "profile_complete" }
 * Custom fields:  { "type": "profile_complete", "fields": ["display_name", "username", "bio"] }
 */
class ProfileCompletenessEvaluator implements BadgeEvaluatorContract
{
    private const DEFAULT_FIELDS = ['display_name', 'username', 'bio', 'avatar', 'location'];

    public function __construct(private readonly array $criteria = []) {}

    public function passes(User $user, ?Model $subject): bool
    {
        $profile = $user->profile;

        if ($profile === null) return false;

        $fields = $this->criteria['fields'] ?? self::DEFAULT_FIELDS;

        foreach ($fields as $field) {
            if (!filled($profile->{$field})) {
                return false;
            }
        }

        return true;
    }
}
