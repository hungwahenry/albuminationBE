<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PublicProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $profile = $user->profile;

        return [
            'id'              => $user->id,
            'username'        => $profile->username,
            'display_name'    => $profile->display_name,
            'avatar'          => $profile->avatar,
            'bio'             => $profile->bio,
            'location'        => $profile->place_name,
            'followers_count' => $profile->followers_count,
            'following_count' => $profile->following_count,
            'rotations_count' => $profile->rotations_count,
            'takes_count'     => $profile->takes_count,
            'is_following'    => Auth::check() && Auth::id() !== $user->id
                ? $request->user()->isFollowing($user->id)
                : false,
            'is_own_profile'  => Auth::check() && Auth::id() === $user->id,
            'created_at'      => $user->created_at->toISOString(),
        ];
    }
}
