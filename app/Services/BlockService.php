<?php

namespace App\Services;

use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BlockService
{
    public function __construct(private FollowService $followService) {}

    public function block(User $blocker, User $target): void
    {
        DB::transaction(function () use ($blocker, $target) {
            $block = Block::firstOrCreate([
                'user_id'         => $blocker->id,
                'blocked_user_id' => $target->id,
            ]);

            // Remove mutual follows
            $this->followService->removeFollower($target, $blocker);
            $this->followService->removeFollower($blocker, $target);

            if ($block->wasRecentlyCreated) {
                activity('moderation')
                    ->causedBy($blocker)
                    ->performedOn($target)
                    ->withProperties(['action' => 'user_blocked'])
                    ->log('User blocked another user');
            }
        });
    }

    public function unblock(User $blocker, User $target): void
    {
        Block::where('user_id', $blocker->id)
            ->where('blocked_user_id', $target->id)
            ->delete();
    }
}
