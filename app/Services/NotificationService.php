<?php

namespace App\Services;

use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use App\Models\Report;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Notifications\CommentOrReplyNotification;
use App\Notifications\ContentLovedNotification;
use App\Notifications\GroupedNotificationStore;
use App\Notifications\NewFollowerNotification;
use App\Notifications\RotationPublishedNotification;
use App\Notifications\ReportUpdatedNotification;
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

    /**
     * Notify a user when someone follows them.
     */
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

    /**
     * Notify the take author and the replied-to user when a reply is posted.
     */
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

    /**
     * Notify the rotation author and the replied-to user when a comment is posted.
     */
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
                    contextSlug: $rotation->slug,
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
                    contextSlug: $rotation->slug,
                ),
                groupKey: "replies:rotation:{$rotation->id}:user:{$replyToUser->id}",
                preferencePrefix: 'reply_content',
            );
        }
    }

    /**
     * Notify all followers when a rotation is published.
     */
    public function onRotationPublished(User $author, Rotation $rotation): void
    {
        if (!$rotation->isPublished()) {
            return;
        }

        User::whereHas('following', function ($q) use ($author) {
            $q->where('following_id', $author->id);
        })
            ->with('profile')
            ->where('id', '!=', $author->id)
            ->chunkById(200, function (Collection $chunk) use ($author, $rotation) {
                foreach ($chunk as $follower) {
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
            });
    }

    /**
     * Notify the content owner when their take, rotation, or album is loved.
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

    /**
     * Notify the reporter when their report is reviewed or dismissed.
     */
    public function onReportResolved(Report $report, string $resolution): void
    {
        $reporter = $report->user;

        if (!$reporter) {
            return;
        }

        $this->notifyWithGrouping(
            recipient: $reporter,
            notification: new ReportUpdatedNotification($report, $resolution),
            groupKey: "report_updated:report:{$report->id}",
            preferencePrefix: 'report_updates',
        );
    }

    /**
     * Notify the take author when someone reacts to their take.
     */
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
            preferencePrefix: 'reaction',
        );
    }

    /**
     * Dispatch a notification respecting user preferences and in-app grouping.
     */
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

}
