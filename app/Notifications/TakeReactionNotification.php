<?php

namespace App\Notifications;

use App\Models\Take;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TakeReactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly Take $take,
        private readonly string $type, // 'agree' or 'disagree'
    ) {
    }

    public function via(object $notifiable): array
    {
        // Channels are selected centrally in NotificationService based on user preferences.
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->type === 'agree' ? 'agreed with' : 'disagreed with';

        return (new MailMessage())
            ->subject('Someone reacted to your take')
            ->line("{$this->actorDisplayName()} {$label} your take.");
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->type === 'agree' ? 'agreed with' : 'disagreed with';

        return [
            'type' => 'take_reaction',
            'reaction_type' => $this->type,
            'take_id' => $this->take->id,
            'album_mbid' => $this->take->album?->mbid,
            'actor_id' => $this->actor->id,
            'actor' => [
                'id' => $this->actor->id,
                'username' => $this->actor->profile?->username,
                'display_name' => $this->actor->profile?->display_name,
                'avatar' => $this->actor->profile?->avatar,
            ],
            'message' => "{$label} your take",
        ];
    }

    private function actorDisplayName(): string
    {
        return $this->actor->profile?->display_name
            ?? $this->actor->profile?->username
            ?? $this->actor->email;
    }
}
