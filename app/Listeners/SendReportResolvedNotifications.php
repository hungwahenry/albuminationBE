<?php

namespace App\Listeners;

use App\Events\ReportResolved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReportResolvedNotifications implements ShouldQueue
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(ReportResolved $event): void
    {
        $this->notifications->onReportResolved($event->report, $event->resolution);
    }
}
