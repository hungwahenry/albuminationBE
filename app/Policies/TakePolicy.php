<?php

namespace App\Policies;

use App\Models\Take;
use App\Models\User;

class TakePolicy
{
    public function update(User $user, Take $take): bool
    {
        return $take->user_id === $user->id;
    }

    public function delete(User $user, Take $take): bool
    {
        return $take->user_id === $user->id;
    }
}
