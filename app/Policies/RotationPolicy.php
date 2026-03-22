<?php

namespace App\Policies;

use App\Models\Rotation;
use App\Models\User;

class RotationPolicy
{
    /**
     * Block all interaction when either user has blocked the other.
     * Owner actions on their own rotations are always allowed.
     */
    public function before(\Illuminate\Contracts\Auth\Authenticatable $user, string $ability, mixed ...$args): ?bool
    {
        // Non-app users (e.g. AdminUser) short-circuit all policy method checks.
        // The admin panel's resource canX() methods handle access control instead.
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

    /**
     * View a rotation: must be public or owned by the user.
     */
    public function view(User $user, Rotation $rotation): bool
    {
        return $rotation->is_public || $rotation->user_id === $user->id;
    }

    /**
     * Update, delete, publish, redraft, manage items: must be the owner.
     */
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

    /**
     * Comment on a rotation: must be published or owned by the user.
     */
    public function comment(User $user, Rotation $rotation): bool
    {
        return $rotation->isPublished() || $rotation->user_id === $user->id;
    }

    /**
     * Love a rotation: must be published or owned by the user.
     */
    public function love(User $user, Rotation $rotation): bool
    {
        return $rotation->isPublished() || $rotation->user_id === $user->id;
    }
}
