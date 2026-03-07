<?php

namespace App\Services;

use App\Models\Love;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LoveService
{
    /**
     * Toggle a love for any loveable model.
     * Increments/decrements the loves_count counter cache on the model.
     *
     * @return array{ loved: bool, loves_count: int }
     */
    public function toggle(User $user, Model $loveable): array
    {
        $existing = Love::where('user_id', $user->id)
            ->where('loveable_type', $loveable->getMorphClass())
            ->where('loveable_id', $loveable->getKey())
            ->first();

        if ($existing) {
            $existing->delete();
            $loveable->decrement('loves_count');

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

        return [
            'loved'       => true,
            'loves_count' => $loveable->loves_count,
        ];
    }
}
