<?php

namespace App\Notifications;

use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ContentLovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $actor,
        private readonly string $contentType,
        private readonly int $contentId,
        private readonly ?string $contentTitle = null,
        private readonly array $navData = [],
    ) {
    }

    public function via(object $notifiable): array
    {
        // Channels are selected centrally in NotificationService based on user preferences.
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->labelForType();

        return (new MailMessage())
            ->subject("Someone loved your {$label}")
            ->line("{$this->actorDisplayName()} loved your {$label}.")
            ->lineIf($this->contentTitle, $this->contentTitle);
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'type'          => 'content_loved',
            'content_type'  => $this->contentType,
            'content_id'    => $this->contentId,
            'content_title' => $this->contentTitle,
            'actor_id'      => $this->actor->id,
            'actor'         => [
                'id'           => $this->actor->id,
                'username'     => $this->actor->profile?->username,
                'display_name' => $this->actor->profile?->display_name,
                'avatar'       => $this->actor->profile?->avatar,
            ],
            'message' => "loved your {$this->labelForType()}",
        ], $this->navData);
    }

    /**
     * Resolve the owning user of a loveable model, or null if notifications are not applicable.
     */
    public static function resolveOwner(Model $loveable): ?User
    {
        return match (true) {
            $loveable instanceof Rotation        => $loveable->user,
            $loveable instanceof RotationComment => $loveable->user,
            $loveable instanceof Take            => $loveable->user,
            $loveable instanceof TakeReply       => $loveable->user,
            default                              => null,
        };
    }

    /**
     * Resolve the notification context (type, id, title, group key, nav data) for a loveable model.
     *
     * @return array{0: string, 1: int, 2: ?string, 3: string, 4: array}
     */
    public static function resolveContext(Model $loveable): array
    {
        if ($loveable instanceof Rotation) {
            return ['rotation', $loveable->id, $loveable->title, "likes:rotation:{$loveable->id}", []];
        }

        if ($loveable instanceof RotationComment) {
            return [
                'rotation_comment',
                $loveable->id,
                Str::limit(trim((string) $loveable->body), 140),
                "likes:rotation_comment:{$loveable->id}",
                ['rotation_id' => $loveable->rotation_id],
            ];
        }

        if ($loveable instanceof Take) {
            $loveable->loadMissing('album');

            return [
                'take',
                $loveable->id,
                Str::limit(trim((string) $loveable->body), 140),
                "likes:take:{$loveable->id}",
                ['album_mbid' => $loveable->album?->mbid],
            ];
        }

        if ($loveable instanceof TakeReply) {
            $loveable->loadMissing('take.album');

            return [
                'take_reply',
                $loveable->id,
                Str::limit(trim((string) $loveable->body), 140),
                "likes:take_reply:{$loveable->take_id}",
                [
                    'take_id'    => $loveable->take_id,
                    'album_mbid' => $loveable->take?->album?->mbid,
                ],
            ];
        }

        return [
            class_basename($loveable),
            $loveable->getKey(),
            null,
            'likes:other:' . $loveable->getMorphClass() . ':' . $loveable->getKey(),
            [],
        ];
    }

    private function labelForType(): string
    {
        return match ($this->contentType) {
            'rotation'         => 'rotation',
            'rotation_comment' => 'comment',
            'take'             => 'take',
            'take_reply'       => 'reply',
            default            => Str::snake($this->contentType, ' '),
        };
    }

    private function actorDisplayName(): string
    {
        return $this->actor->profile?->display_name
            ?? $this->actor->profile?->username
            ?? $this->actor->email;
    }
}
