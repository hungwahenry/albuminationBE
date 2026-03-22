<?php

namespace App\Events;

use App\Models\Take;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TakeReactionChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $actor,
        public readonly Take $take,
        public readonly string $type, // agree or disagree
    ) {
    }
}

