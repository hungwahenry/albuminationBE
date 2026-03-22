<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentLoved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $actor,
        public readonly Model $loveable,
    ) {
    }
}

