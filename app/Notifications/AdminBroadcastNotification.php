<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminBroadcastNotification extends Notification
{
    use Queueable;

    /**
     * @param  string       $title
     * @param  string       $body
     * @param  string       $sentBy
     * @param  string|null  $intentType    'screen' | 'url' | null
     * @param  string|null  $intentTarget  Screen key (e.g. 'home') or full URL
     */
    public function __construct(
        private readonly string  $title,
        private readonly string  $body,
        private readonly string  $sentBy,
        private readonly ?string $intentType   = null,
        private readonly ?string $intentTarget = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return [];
    }

    public function toDatabase(object $notifiable): array
    {
        $payload = [
            'type'    => 'broadcast',
            'title'   => $this->title,
            'message' => $this->body,
            'sent_by' => $this->sentBy,
        ];

        if ($this->intentType && $this->intentTarget) {
            $payload['intent'] = [
                'type'   => $this->intentType,
                'target' => $this->intentTarget,
            ];
        }

        return $payload;
    }
}
