<?php

namespace App\Listeners;

use App\Events\RotationCommentCreated;
use App\Jobs\EvaluateBadgesJob;
use App\Models\RotationComment;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnRotationCommentCreated implements ShouldQueue
{
    public function handle(RotationCommentCreated $event): void
    {
        EvaluateBadgesJob::dispatch(
            'rotation_comment_created',
            $event->commenter->id,
            RotationComment::class,
            $event->comment->id,
        );
    }
}
