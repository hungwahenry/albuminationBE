<?php

namespace App\Listeners;

use App\Events\RotationPublished;
use App\Jobs\EvaluateBadgesJob;
use App\Models\Rotation;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnRotationPublished implements ShouldQueue
{
    public function handle(RotationPublished $event): void
    {
        EvaluateBadgesJob::dispatch(
            'rotation_published',
            $event->author->id,
            Rotation::class,
            $event->rotation->id,
        );
    }
}
