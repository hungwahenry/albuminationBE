<?php

namespace App\Listeners;

use App\Events\ContentLoved;
use App\Jobs\EvaluateBadgesJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnContentLoved implements ShouldQueue
{
    public function handle(ContentLoved $event): void
    {
        // Badges for the person giving the love
        EvaluateBadgesJob::dispatch('love_given', $event->actor->id);

        // Badges for the owner of the loved content
        $owner = $event->loveable->user ?? null;
        if ($owner && $owner->id !== $event->actor->id) {
            EvaluateBadgesJob::dispatch(
                'love_received',
                $owner->id,
                $event->loveable::class,
                $event->loveable->id,
            );
        }
    }
}
