<?php

namespace App\Traits;

use App\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Reportable
{
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    public function isReportedBy(int $userId): bool
    {
        if ($this->relationLoaded('reports')) {
            return $this->reports->where('user_id', $userId)->isNotEmpty();
        }

        return $this->reports()->where('user_id', $userId)->exists();
    }
}
