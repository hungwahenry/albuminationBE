<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class RotationCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'rotation_id'       => $this->rotation_id,
            'parent_id'         => $this->parent_id,
            'user'              => [
                'id'           => $this->user->id,
                'username'     => $this->user->profile->username,
                'display_name' => $this->user->profile->display_name,
                'avatar'       => $this->user->profile->avatar,
            ],
            'reply_to_username' => $this->replyToUser?->profile?->username,
            'body'              => $this->is_deleted ? null : $this->body,
            'gif_url'           => $this->is_deleted ? null : $this->gif_url,
            'is_deleted'        => $this->is_deleted,
            'loves_count'       => $this->loves_count,
            'replies_count'     => $this->replies_count ?? 0,
            'is_loved'          => Auth::check() ? $this->isLovedBy(Auth::id()) : false,
            'is_mine'           => Auth::id() === $this->user_id,
            'created_at'        => $this->created_at->toISOString(),
        ];
    }
}
