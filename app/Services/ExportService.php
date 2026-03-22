<?php

namespace App\Services;

use App\Http\Resources\Export\ExportProfileResource;
use App\Http\Resources\Export\ExportRotationResource;
use App\Http\Resources\Export\ExportTakeResource;
use App\Models\User;
use Illuminate\Http\Request;

class ExportService
{
    /**
     * Builds a complete, clean export payload for the given user.
     * Eager-loads all required relations in a single pass to avoid N+1.
     */
    public function build(User $user, Request $request): array
    {
        $user->loadMissing([
            'profile',
            'rotations.vibetags',
            'rotations.items.album.artists',
            'rotations.items.track.artists',
            'rotations.items.track.album',
            'takes.album.artists',
        ]);

        return [
            'exported_at' => now()->toISOString(),
            'profile'     => (new ExportProfileResource($user))->toArray($request),
            'rotations'   => ExportRotationResource::collection($user->rotations)->toArray($request),
            'takes'       => ExportTakeResource::collection($user->takes)->toArray($request),
        ];
    }
}
