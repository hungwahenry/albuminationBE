<?php

namespace App\Listeners;

use App\Events\ReportResolved;
use App\Jobs\EvaluateBadgesJob;
use App\Models\Report;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvaluateBadgesOnReportResolved implements ShouldQueue
{
    public function handle(ReportResolved $event): void
    {
        // Only award when resolved in the reporter's favour, not dismissed
        if ($event->resolution !== 'resolved') {
            return;
        }

        EvaluateBadgesJob::dispatch(
            'report_resolved',
            $event->report->user_id,
            Report::class,
            $event->report->id,
        );
    }
}
