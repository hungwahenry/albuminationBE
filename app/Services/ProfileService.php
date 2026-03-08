<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function findByUsername(string $username, array $with = []): ?Profile
    {
        return Profile::where('username', $username)->with($with)->first();
    }

    public function getRotations(User $user, User $viewer): LengthAwarePaginator
    {
        $query = $user->rotations()
            ->with(['vibetags', 'user.profile'])
            ->where('status', 'published')
            ->latest('published_at');

        if ($viewer->id !== $user->id) {
            $query->where('is_public', true);
        }

        return $query->paginate(20);
    }

    public function getTakes(User $user): LengthAwarePaginator
    {
        return $user->takes()
            ->with(['user.profile', 'album.artists'])
            ->where('is_deleted', false)
            ->latest()
            ->paginate(20);
    }

    public function update(User $user, array $data): User
    {
        $profile = $user->profile;

        if (!empty($data['avatar'])) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $data['avatar']->store('avatars', 'public');
        }

        $profile->update($data);

        return $user->load(['profile.headerAlbum.artists', 'profile.pinnedRotation', 'profile.currentVibe.artists']);
    }
}
