<?php

namespace App\Services;

use App\Events\ContentLoved;
use App\Models\Love;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LoveService
{
    /**
     * Toggle a love for any loveable model.
    */
    public function toggle(User $user, Model $loveable): array
    {
        return DB::transaction(function () use ($user, $loveable) {
            $existing = Love::where('user_id', $user->id)
                ->where('loveable_type', $loveable->getMorphClass())
                ->where('loveable_id', $loveable->getKey())
                ->first();

            $ownerId = $loveable->getAttribute('user_id');

            if ($existing) {
                $existing->delete();
                $loveable->decrement('loves_count');

                // Decrement loves_received_count on the owner's profile (exclude self-loves)
                if ($ownerId && $ownerId !== $user->id) {
                    Profile::where('user_id', $ownerId)->decrement('loves_received_count');
                }

                return [
                    'loved'       => false,
                    'loves_count' => max(0, $loveable->loves_count),
                ];
            }

            Love::create([
                'user_id'       => $user->id,
                'loveable_type' => $loveable->getMorphClass(),
                'loveable_id'   => $loveable->getKey(),
            ]);

            $loveable->increment('loves_count');

            // Increment loves_received_count on the owner's profile (exclude self-loves)
            if ($ownerId && $ownerId !== $user->id) {
                Profile::where('user_id', $ownerId)->increment('loves_received_count');
            }

            ContentLoved::dispatch($user, $loveable);

            return [
                'loved'       => true,
                'loves_count' => $loveable->loves_count,
            ];
        });
    }
}
