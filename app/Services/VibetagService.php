<?php

namespace App\Services;

use App\Models\Vibetag;
use Illuminate\Support\Facades\DB;

class VibetagService
{
    /**
     * Sync vibetags for a rotation. Handles creation, linking, and usage counts.
     *
     * @param  \App\Models\Rotation  $rotation
     * @param  string[]              $tagNames  Raw tag names (with or without ~ prefix)
     */
    public function sync($rotation, array $tagNames): void
    {
        DB::transaction(function () use ($rotation, $tagNames) {
            $normalized = collect($tagNames)
                ->map(fn (string $name) => strtolower(trim(ltrim(trim($name), '~'))))
                ->filter(fn (string $name) => $name !== '')
                ->unique()
                ->values();

            $existing = Vibetag::whereIn('name', $normalized)->get()->keyBy('name');

            $ids = [];
            foreach ($normalized as $name) {
                if ($existing->has($name)) {
                    $ids[] = $existing[$name]->id;
                } else {
                    $tag = Vibetag::create(['name' => $name]);
                    $ids[] = $tag->id;
                }
            }

            // Detaching tags — decrement their usage count
            $detaching = $rotation->vibetags()->whereNotIn('vibetags.id', $ids)->pluck('vibetags.id');
            if ($detaching->isNotEmpty()) {
                Vibetag::whereIn('id', $detaching)->where('usage_count', '>', 0)->decrement('usage_count');
            }

            // Attaching new tags — increment their usage count
            $currentIds = $rotation->vibetags()->pluck('vibetags.id')->toArray();
            $attaching = array_diff($ids, $currentIds);
            if (!empty($attaching)) {
                Vibetag::whereIn('id', $attaching)->increment('usage_count');
            }

            $rotation->vibetags()->sync($ids);
        });
    }

    /**
     * Detach all vibetags and decrement usage counts (for rotation deletion).
     */
    public function detachAll($rotation): void
    {
        DB::transaction(function () use ($rotation) {
            $tagIds = $rotation->vibetags()->pluck('vibetags.id');
            if ($tagIds->isNotEmpty()) {
                Vibetag::whereIn('id', $tagIds)->where('usage_count', '>', 0)->decrement('usage_count');
            }
            $rotation->vibetags()->detach();
        });
    }
}
