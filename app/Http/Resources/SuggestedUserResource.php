<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SuggestedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->user_id,
            'username'        => $this->username,
            'display_name'    => $this->display_name,
            'avatar'          => $this->avatar,
            'followers_count' => $this->followers_count,
            'is_following'    => Auth::check() ? Auth::user()->isFollowing($this->user_id) : false,
        ];
    }
}
