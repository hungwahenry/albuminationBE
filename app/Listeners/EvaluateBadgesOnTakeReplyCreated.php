<?php

namespace App\Listeners;

use App\Events\TakeReplyCreated;
use App\Jobs\EvaluateBadgesJob;
use App\Models\TakeReply;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnTakeReplyCreated implements ShouldQueue
{
    public function handle(TakeReplyCreated $event): void
    {
        EvaluateBadgesJob::dispatch(
            'take_reply_created',
            $event->replier->id,
            TakeReply::class,
            $event->reply->id,
        );
    }
}
