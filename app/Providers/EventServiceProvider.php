<?php

namespace App\Providers;

use App\Events\BadgeEarned;
use App\Events\ContentLoved;
use App\Events\FollowCreated;
use App\Events\ReportResolved;
use App\Events\RotationCommentCreated;
use App\Events\RotationPublished;
use App\Events\StanCreated;
use App\Events\TakeReactionChanged;
use App\Events\TakeReplyCreated;
use App\Listeners\EvaluateBadgesOnContentLoved;
use App\Listeners\EvaluateBadgesOnFollowCreated;
use App\Listeners\EvaluateBadgesOnRotationCommentCreated;
use App\Listeners\EvaluateBadgesOnRotationPublished;
use App\Listeners\EvaluateBadgesOnStanCreated;
use App\Listeners\EvaluateBadgesOnTakeReactionChanged;
use App\Listeners\EvaluateBadgesOnReportResolved;
use App\Listeners\EvaluateBadgesOnTakeReplyCreated;
use App\Listeners\SendBadgeEarnedNotification;
use App\Listeners\SendContentLovedNotifications;
use App\Listeners\SendFollowNotifications;
use App\Listeners\SendReportResolvedNotifications;
use App\Listeners\SendRotationCommentNotifications;
use App\Listeners\SendRotationPublishedNotifications;
use App\Listeners\SendTakeReactionNotifications;
use App\Listeners\SendTakeReplyNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FollowCreated::class => [
            SendFollowNotifications::class,
            EvaluateBadgesOnFollowCreated::class,
        ],
        TakeReplyCreated::class => [
            SendTakeReplyNotifications::class,
            EvaluateBadgesOnTakeReplyCreated::class,
        ],
        RotationCommentCreated::class => [
            SendRotationCommentNotifications::class,
            EvaluateBadgesOnRotationCommentCreated::class,
        ],
        RotationPublished::class => [
            SendRotationPublishedNotifications::class,
            EvaluateBadgesOnRotationPublished::class,
        ],
        ContentLoved::class => [
            SendContentLovedNotifications::class,
            EvaluateBadgesOnContentLoved::class,
        ],
        TakeReactionChanged::class => [
            SendTakeReactionNotifications::class,
            EvaluateBadgesOnTakeReactionChanged::class,
        ],
        StanCreated::class => [
            EvaluateBadgesOnStanCreated::class,
        ],
        ReportResolved::class => [
            SendReportResolvedNotifications::class,
            EvaluateBadgesOnReportResolved::class,
        ],
        BadgeEarned::class => [
            SendBadgeEarnedNotification::class,
        ],
    ];
}
