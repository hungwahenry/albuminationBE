<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HiddenFromBlockedUsers
{
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if (!$viewer) {
            return $query;
        }

        $blockedIds = $viewer->blockedUserIds();

        if ($blockedIds->isEmpty()) {
            return $query;
        }

        return $query->whereNotIn($this->qualifyColumn('user_id'), $blockedIds);
    }
}
