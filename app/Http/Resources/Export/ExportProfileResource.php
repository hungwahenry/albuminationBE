<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a User model (with loaded profile relation) for data export.
 * Only includes data that belongs to the user — no internal IDs, tokens, or system fields.
 */
class ExportProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'username'        => $this->profile?->username,
            'display_name'    => $this->profile?->display_name,
            'bio'             => $this->profile?->bio,
            'gender'          => $this->profile?->gender,
            'location'        => $this->profile?->place_name,
            'email'           => $this->email,
            'email_verified'  => $this->email_verified_at !== null,
            'member_since'    => $this->created_at->toDateString(),
            'followers_count' => $this->profile?->followers_count ?? 0,
            'following_count' => $this->profile?->following_count ?? 0,
            'rotations_count' => $this->profile?->rotations_count ?? 0,
            'takes_count'     => $this->profile?->takes_count ?? 0,
        ];
    }
}
