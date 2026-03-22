<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Report $report,
        private readonly string $resolution,
    ) {
    }

    public function via(object $notifiable): array
    {
        // Channels are selected centrally in NotificationService based on user preferences.
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->resolution === 'resolved'
            ? 'has been reviewed and action has been taken'
            : 'has been reviewed and no action was required';

        return (new MailMessage())
            ->subject('Your report has been reviewed')
            ->line("A report you submitted {$label}.")
            ->line('Thank you for helping keep Albumination safe.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'report_updated',
            'report_id'  => $this->report->id,
            'resolution' => $this->resolution,
            'message'    => $this->resolution === 'resolved'
                ? 'Your report was reviewed and action was taken'
                : 'Your report was reviewed — no action was required',
        ];
    }
}
