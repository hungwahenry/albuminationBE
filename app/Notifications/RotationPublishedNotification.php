<?php

namespace App\Notifications;

use App\Models\Rotation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RotationPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $author,
        private readonly Rotation $rotation,
    ) {
    }

    public function via(object $notifiable): array
    {
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $authorName = $this->author->profile?->display_name
            ?? $this->author->profile?->username
            ?? $this->author->email;

        $title = $this->rotation->title;
        $suffix = $title ? ': ' . $title : '';

        return (new MailMessage())
            ->subject("New rotation from {$authorName}")
            ->line("{$authorName} published a new rotation{$suffix}.");
    }

    public function toDatabase(object $notifiable): array
    {
        $authorName = $this->author->profile?->display_name
            ?? $this->author->profile?->username
            ?? $this->author->email;

        return [
            'type' => 'rotation_published',
            'rotation_id' => $this->rotation->id,
            'rotation_slug' => $this->rotation->slug,
            'rotation_title' => $this->rotation->title,
            'actor_id' => $this->author->id,
            'actor' => [
                'id' => $this->author->id,
                'username' => $this->author->profile?->username,
                'display_name' => $this->author->profile?->display_name,
                'avatar' => $this->author->profile?->avatar,
            ],
            'message' => "published a new rotation",
        ];
    }
}

