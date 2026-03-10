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
            Block::firstOrCreate([
                'user_id'         => $blocker->id,
                'blocked_user_id' => $target->id,
            ]);

            // Remove mutual follows
            $this->followService->removeFollower($target, $blocker);
            $this->followService->removeFollower($blocker, $target);
        });
    }

    public function unblock(User $blocker, User $target): void
    {
        Block::where('user_id', $blocker->id)
            ->where('blocked_user_id', $target->id)
            ->delete();
    }
}
