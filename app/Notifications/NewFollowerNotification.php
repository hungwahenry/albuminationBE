<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $follower,
    ) {
    }

    public function via(object $notifiable): array
    {
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $displayName = $this->follower->profile?->display_name
            ?? $this->follower->profile?->username
            ?? $this->follower->email;

        $username = $this->follower->profile?->username ?? '';

        return (new MailMessage())
            ->subject('New follower on Albumination')
            ->line("{$displayName} started following you.")
            ->when($username !== '', function (MailMessage $message) use ($username) {
                return $message->action('View profile', url("/users/{$username}"));
            });
        }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_follower',
            'actor_id' => $this->follower->id,
            'actor' => [
                'id' => $this->follower->id,
                'username' => $this->follower->profile?->username,
                'display_name' => $this->follower->profile?->display_name,
                'avatar' => $this->follower->profile?->avatar,
            ],
            'message' => 'started following you',
        ];
    }
}

