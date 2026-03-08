<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vibetag extends Model
{
    protected $fillable = ['name', 'usage_count'];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    public function rotations(): BelongsToMany
    {
        return $this->belongsToMany(Rotation::class);
    }
}
