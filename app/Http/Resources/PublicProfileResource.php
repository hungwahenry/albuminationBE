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
            'header_album'    => $this->formatHeaderAlbum($profile),
            'pinned_rotation' => $this->formatPinnedRotation($profile),
            'current_vibe'    => $this->formatCurrentVibe($profile),
            'created_at'      => $user->created_at->toISOString(),
        ];
    }

    private function formatHeaderAlbum($profile): ?array
    {
        $album = $profile->headerAlbum;
        if (!$album) return null;

        return [
            'id'            => $album->id,
            'mbid'          => $album->mbid,
            'title'         => $album->title,
            'artist_name'   => $album->artists->pluck('name')->join(', '),
            'cover_art_url' => $album->cover_art_url,
        ];
    }

    private function formatPinnedRotation($profile): ?array
    {
        $rotation = $profile->pinnedRotation;
        if (!$rotation || $rotation->status !== 'published') return null;

        return [
            'id'              => $rotation->id,
            'slug'            => $rotation->slug,
            'title'           => $rotation->title,
            'cover_image_url' => $rotation->cover_image,
            'items_count'     => $rotation->items_count,
            'loves_count'     => $rotation->loves_count,
        ];
    }

    private function formatCurrentVibe($profile): ?array
    {
        $vibe = $profile->currentVibe;
        if (!$vibe) return null;

        $type = $profile->current_vibe_type === 'App\\Models\\Album' ? 'album' : 'track';
        $artistName = $vibe->artists->pluck('name')->join(', ');

        return [
            'type'          => $type,
            'id'            => $vibe->id,
            'mbid'          => $vibe->mbid,
            'title'         => $vibe->title,
            'artist_name'   => $artistName,
            'cover_art_url' => $type === 'album'
                ? $vibe->cover_art_url
                : $vibe->album?->cover_art_url,
        ];
    }
}
