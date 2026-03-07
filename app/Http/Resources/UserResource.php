<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'onboarding_completed' => $this->hasCompletedOnboarding(),
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'created_at' => $this->created_at,
        ];
    }
}
