<?php

namespace App\Services;

use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Notifications\CommentOrReplyNotification;
use App\Notifications\ContentLovedNotification;
use App\Notifications\GroupedNotificationStore;
use App\Notifications\NewFollowerNotification;
use App\Notifications\RotationPublishedNotification;
use App\Notifications\TakeReactionNotification;
use App\Notifications\Channels\ExpoPushChannel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(private readonly GroupedNotificationStore $store) {}

    public function onFollowCreated(User $follower, User $target): void
    {
        if ($follower->id === $target->id) {
            return;
        }

        $this->notifyWithGrouping(
            recipient: $target,
            notification: new NewFollowerNotification($follower),
            groupKey: "follow:user:{$target->id}",
            preferencePrefix: 'new_follower',
        );
    }

    public function onTakeReplyCreated(User $replier, Take $take, ?User $replyToUser, TakeReply $reply): void
    {
        $excerpt = Str::limit(trim((string) $reply->body), 140);
        $take->loadMissing('album');
        $albumMbid = $take->album?->mbid;

        if ($take->user_id !== $replier->id) {
            $this->notifyWithGrouping(
                recipient: $take->user,
                notification: new CommentOrReplyNotification(
                    actor: $replier,
                    contextType: 'take',
                    contextId: $take->id,
                    kind: 'comment',
                    excerpt: $excerpt,
                    albumMbid: $albumMbid,
                ),
                groupKey: "comments:take:{$take->id}",
                preferencePrefix: 'comment_content',
            );
        }

        if ($replyToUser && $replyToUser->id !== $replier->id && $replyToUser->id !== $take->user_id) {
            $this->notifyWithGrouping(
                recipient: $replyToUser,
                notification: new CommentOrReplyNotification(
                    actor: $replier,
                    contextType: 'take',
                    contextId: $take->id,
                    kind: 'reply',
                    excerpt: $excerpt,
                    albumMbid: $albumMbid,
                ),
                groupKey: "replies:take:{$take->id}:user:{$replyToUser->id}",
                preferencePrefix: 'reply_content',
            );
        }
    }

    public function onRotationCommentCreated(User $commenter, Rotation $rotation, ?User $replyToUser, RotationComment $comment): void
    {
        $excerpt = Str::limit(trim((string) $comment->body), 140);

        if ($rotation->user_id !== $commenter->id) {
            $this->notifyWithGrouping(
                recipient: $rotation->user,
                notification: new CommentOrReplyNotification(
                    actor: $commenter,
                    contextType: 'rotation',
                    contextId: $rotation->id,
                    kind: 'comment',
                    excerpt: $excerpt,
                ),
                groupKey: "comments:rotation:{$rotation->id}",
                preferencePrefix: 'comment_content',
            );
        }

        if ($replyToUser && $replyToUser->id !== $commenter->id && $replyToUser->id !== $rotation->user_id) {
            $this->notifyWithGrouping(
                recipient: $replyToUser,
                notification: new CommentOrReplyNotification(
                    actor: $commenter,
                    contextType: 'rotation',
                    contextId: $rotation->id,
                    kind: 'reply',
                    excerpt: $excerpt,
                ),
                groupKey: "replies:rotation:{$rotation->id}:user:{$replyToUser->id}",
                preferencePrefix: 'reply_content',
            );
        }
    }

    public function onRotationPublished(User $author, Rotation $rotation): void
    {
        if (!$rotation->isPublished()) {
            return;
        }

        $followers = $this->followersOf($author);

        if ($followers->isEmpty()) {
            return;
        }

        foreach ($followers as $follower) {
            if ($follower->id === $author->id) {
                continue;
            }

            if ($follower->hasBlocked($author->id) || $follower->isBlockedBy($author->id)) {
                continue;
            }

            $this->notifyWithGrouping(
                recipient: $follower,
                notification: new RotationPublishedNotification($author, $rotation),
                groupKey: "rotation_published:author:{$author->id}",
                preferencePrefix: 'rotation_published',
            );
        }
    }

    /**
     * Generic handler for loves/likes on loveable content.
     * Supports Rotation, RotationComment, Take, and TakeReply.
     */
    public function onContentLoved(User $actor, Model $loveable): void
    {
        $owner = ContentLovedNotification::resolveOwner($loveable);

        if (!$owner || $owner->id === $actor->id) {
            return;
        }

        [$contentType, $contentId, $title, $groupKey, $navData] = ContentLovedNotification::resolveContext($loveable);

        $this->notifyWithGrouping(
            recipient: $owner,
            notification: new ContentLovedNotification(
                actor: $actor,
                contentType: $contentType,
                contentId: $contentId,
                contentTitle: $title,
                navData: $navData,
            ),
            groupKey: $groupKey,
            preferencePrefix: 'like_content',
        );
    }

    public function onTakeReacted(User $actor, Take $take, string $type): void
    {
        if ($take->user_id === $actor->id) {
            return;
        }

        $take->loadMissing('album');

        $this->notifyWithGrouping(
            recipient: $take->user,
            notification: new TakeReactionNotification($actor, $take, $type),
            groupKey: "reactions:take:{$take->id}",
            preferencePrefix: 'like_content',
        );
    }

    private function notifyWithGrouping(
        User $recipient,
        object $notification,
        string $groupKey,
        string $preferencePrefix,
    ): void {
        $prefs = UserNotificationPreference::firstOrCreate(['user_id' => $recipient->id])->toArray();

        $sendInApp = Arr::get($prefs, "{$preferencePrefix}_in_app", true);
        $sendMail  = Arr::get($prefs, "{$preferencePrefix}_mail", false);
        $sendPush  = Arr::get($prefs, "{$preferencePrefix}_push", false);

        if (!$sendInApp && !$sendMail && !$sendPush) {
            return;
        }

        $isNewGroup = $sendInApp
            ? $this->store->store($recipient, $notification, $groupKey)
            : true;

        if ($sendMail) {
            Notification::sendNow($recipient, $notification, ['mail']);
        }

        if ($sendPush && $isNewGroup) {
            Notification::sendNow($recipient, $notification, [ExpoPushChannel::class]);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function followersOf(User $user): Collection
    {
        return User::whereHas('following', function ($q) use ($user) {
            $q->where('following_id', $user->id);
        })->with('profile')->get();
    }
}
