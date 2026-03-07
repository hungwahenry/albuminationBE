<?php

namespace App\Traits;

use App\Models\Love;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Loveable
{
    public function loves(): MorphMany
    {
        return $this->morphMany(Love::class, 'loveable');
    }

    public function isLovedBy(int $userId): bool
    {
        return $this->loves()->where('user_id', $userId)->exists();
    }
}
