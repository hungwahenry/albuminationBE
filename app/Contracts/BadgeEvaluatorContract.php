<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface BadgeEvaluatorContract
{
    /**
     * Determine whether the badge criteria is met for the given user and subject.
     *
     * @param  User         $user    The user being evaluated
     * @param  Model|null   $subject The model that triggered evaluation (e.g. a Take, Rotation)
     */
    public function passes(User $user, ?Model $subject): bool;
}
