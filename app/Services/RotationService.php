<?php

namespace App\Services;

use App\Events\RotationPublished;
use App\Models\Album;
use App\Models\Profile;
use App\Models\Rotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RotationService
{
    public function __construct(private VibetagService $vibetagService) {}

    public function create(User $user, array $data): Rotation
    {
        return DB::transaction(function () use ($user, $data) {
            $rotation = $user->rotations()->create([
                'title' => $data['title'],
                'caption' => $data['caption'] ?? null,
                'type' => $data['type'],
                'is_ranked' => $data['is_ranked'] ?? false,
                'is_public' => $data['is_public'] ?? true,
                'status' => 'draft',
            ]);

            if (!empty($data['cover_image'])) {
                $path = $data['cover_image']->store('rotation-covers', 'public');
                $rotation->update(['cover_image' => $path]);
            }

            if (!empty($data['vibetags'])) {
                $this->vibetagService->sync($rotation, $data['vibetags']);
            }

            return $rotation->load('vibetags');
        });
    }

    public function update(Rotation $rotation, array $data): Rotation
    {
        return DB::transaction(function () use ($rotation, $data) {
            $rotation->update(array_filter([
                'title' => $data['title'] ?? null,
                'caption' => array_key_exists('caption', $data) ? $data['caption'] : null,
                'is_ranked' => $data['is_ranked'] ?? null,
                'is_public' => $data['is_public'] ?? null,
            ], fn ($v) => $v !== null));

            if (!empty($data['cover_image'])) {
                if ($rotation->cover_image) {
                    Storage::disk('public')->delete($rotation->cover_image);
                }
                $path = $data['cover_image']->store('rotation-covers', 'public');
                $rotation->update(['cover_image' => $path]);
            }

            if (array_key_exists('vibetags', $data)) {
                $this->vibetagService->sync($rotation, $data['vibetags'] ?? []);
            }

            return $rotation->load('vibetags');
        });
    }

    public function publish(Rotation $rotation): Rotation
    {
        return DB::transaction(function () use ($rotation) {
            $rotation->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $rotation->user->profile->increment('rotations_count');

            $rotation->load('user.profile');

            RotationPublished::dispatch($rotation->user, $rotation);

            return $rotation;
        });
    }

    public function redraft(Rotation $rotation): Rotation
    {
        return DB::transaction(function () use ($rotation) {
            $rotation->update([
                'status' => 'draft',
                'published_at' => null,
            ]);

            $rotation->user->profile->decrement('rotations_count');

            // Clear any profiles that have this rotation pinned
            Profile::where('pinned_rotation_id', $rotation->id)->update(['pinned_rotation_id' => null]);

            return $rotation;
        });
    }

    public function delete(Rotation $rotation): void
    {
        DB::transaction(function () use ($rotation) {
            if ($rotation->status === 'published') {
                $rotation->user->profile->decrement('rotations_count');
            }

            // Decrement rotations_count on albums before items are cascade-deleted at DB level
            $albumIds = $rotation->items()->whereNotNull('album_id')->pluck('album_id');
            if ($albumIds->isNotEmpty()) {
                Album::whereIn('id', $albumIds)->decrement('rotations_count');
            }

            // Clear any profiles that have this rotation pinned
            Profile::where('pinned_rotation_id', $rotation->id)->update(['pinned_rotation_id' => null]);

            $this->vibetagService->detachAll($rotation);

            if ($rotation->cover_image) {
                Storage::disk('public')->delete($rotation->cover_image);
            }

            $rotation->delete();
        });
    }
}
