<?php

namespace App\Services;

use App\Models\Take;
use App\Models\TakeReaction;
use App\Models\User;

class TakeReactionService
{
    /**
     * React to a take with agree or disagree.
     * - Same type as existing → remove reaction
     * - Different type → switch reaction
     * - No existing → create
     *
     * @return array{ type: string|null, agrees_count: int, disagrees_count: int }
     */
    public function react(User $user, Take $take, string $type): array
    {
        $existing = TakeReaction::where('user_id', $user->id)
            ->where('take_id', $take->id)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Remove reaction
                $existing->delete();
                $take->decrement("{$type}s_count");

                return [
                    'type'            => null,
                    'agrees_count'    => max(0, $take->agrees_count),
                    'disagrees_count' => max(0, $take->disagrees_count),
                ];
            }

            // Switch from one reaction to another
            $oldType = $existing->type;
            $existing->update(['type' => $type]);
            $take->decrement("{$oldType}s_count");
            $take->increment("{$type}s_count");

            return [
                'type'            => $type,
                'agrees_count'    => max(0, $take->agrees_count),
                'disagrees_count' => max(0, $take->disagrees_count),
            ];
        }

        TakeReaction::create([
            'user_id' => $user->id,
            'take_id' => $take->id,
            'type'    => $type,
        ]);

        $take->increment("{$type}s_count");

        return [
            'type'            => $type,
            'agrees_count'    => $take->agrees_count,
            'disagrees_count' => $take->disagrees_count,
        ];
    }
}
