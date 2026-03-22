<?php

namespace App\Providers;

use App\Events\ContentLoved;
use App\Events\FollowCreated;
use App\Events\RotationCommentCreated;
use App\Events\RotationPublished;
use App\Events\TakeReactionChanged;
use App\Events\TakeReplyCreated;
use App\Listeners\SendContentLovedNotifications;
use App\Listeners\SendFollowNotifications;
use App\Listeners\SendRotationCommentNotifications;
use App\Listeners\SendRotationPublishedNotifications;
use App\Listeners\SendTakeReactionNotifications;
use App\Listeners\SendTakeReplyNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        FollowCreated::class => [
            SendFollowNotifications::class,
        ],
        TakeReplyCreated::class => [
            SendTakeReplyNotifications::class,
        ],
        RotationCommentCreated::class => [
            SendRotationCommentNotifications::class,
        ],
        RotationPublished::class => [
            SendRotationPublishedNotifications::class,
        ],
        ContentLoved::class => [
            SendContentLovedNotifications::class,
        ],
        TakeReactionChanged::class => [
            SendTakeReactionNotifications::class,
        ],
    ];
}

