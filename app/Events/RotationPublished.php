<?php

namespace App\Events;

use App\Models\Rotation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RotationPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $author,
        public readonly Rotation $rotation,
    ) {
    }
}

