<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TakeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user'            => [
                'id'           => $this->user->id,
                'username'     => $this->user->profile->username,
                'display_name' => $this->user->profile->display_name,
                'avatar'       => $this->user->profile->avatar,
            ],
            'rating'          => $this->is_deleted ? null : $this->rating,
            'body'            => $this->is_deleted ? null : $this->body,
            'is_deleted'      => $this->is_deleted,
            'is_edited'       => $this->edited_at !== null,
            'can_edit'        => !$this->is_deleted && $this->edited_at === null && Auth::id() === $this->user_id,
            'is_mine'         => Auth::id() === $this->user_id,
            'agrees_count'    => $this->agrees_count,
            'disagrees_count' => $this->disagrees_count,
            'replies_count'   => $this->replies_count,
            'user_reaction'   => Auth::check() ? $this->getUserReaction(Auth::id()) : null,
            'created_at'      => $this->created_at->toISOString(),
        ];
    }
}
