<?php

namespace App\Policies;

use App\Models\Rotation;
use App\Models\User;

class RotationPolicy
{
    public function before(\Illuminate\Contracts\Auth\Authenticatable $user, string $ability, mixed ...$args): ?bool
    {
        if (!$user instanceof User) {
            return true;
        }

        $rotation = $args[0] ?? null;

        if ($rotation instanceof Rotation && $rotation->user_id !== $user->id) {
            if ($user->hasBlocked($rotation->user_id) || $user->isBlockedBy($rotation->user_id)) {
                return false;
            }
        }

        return null;
    }

    public function view(User $user, Rotation $rotation): bool
    {
        return $rotation->is_public || $rotation->user_id === $user->id;
    }

   public function update(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function delete(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function publish(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function redraft(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function addItem(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function removeItem(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

    public function reorder(User $user, Rotation $rotation): bool
    {
        return $rotation->user_id === $user->id;
    }

   public function comment(User $user, Rotation $rotation): bool
    {
        return $rotation->isPublished() || $rotation->user_id === $user->id;
    }

    public function love(User $user, Rotation $rotation): bool
    {
        return $rotation->isPublished() || $rotation->user_id === $user->id;
    }
}
