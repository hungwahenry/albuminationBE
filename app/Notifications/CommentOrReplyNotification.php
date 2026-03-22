<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentOrReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly string $contextType, // e.g. take, rotation
        private readonly int $contextId,
        private readonly string $kind, // 'comment' or 'reply'
        private readonly ?string $excerpt = null,
        private readonly ?string $albumMbid = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        // Channels are selected centrally in NotificationService based on user preferences.
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->kind === 'reply'
            ? 'Someone replied to you'
            : 'New comment on your content';

        $message = $this->kind === 'reply'
            ? 'replied to you'
            : 'commented on your content';

        $mail = (new MailMessage())
            ->subject($subject)
            ->line("{$this->actorDisplayName()} {$message}.");

        if ($this->excerpt) {
            $mail->line("\"{$this->excerpt}\"");
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        $message = $this->kind === 'reply' ? 'replied to you' : 'commented on your content';

        return [
            'type' => $this->kind === 'reply' ? 'reply' : 'comment',
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'kind' => $this->kind,
            'excerpt' => $this->excerpt,
            'album_mbid' => $this->albumMbid,
            'actor_id' => $this->actor->id,
            'actor' => [
                'id' => $this->actor->id,
                'username' => $this->actor->profile?->username,
                'display_name' => $this->actor->profile?->display_name,
                'avatar' => $this->actor->profile?->avatar,
            ],
            'message' => $message,
        ];
    }

    private function actorDisplayName(): string
    {
        return $this->actor->profile?->display_name
            ?? $this->actor->profile?->username
            ?? $this->actor->email;
    }
}

