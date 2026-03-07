<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'display_name' => $this->display_name,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'gender' => $this->gender,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'place_name' => $this->place_name,
            ],
        ];
    }
}
