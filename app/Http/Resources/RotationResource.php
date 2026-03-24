<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class RotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'slug'            => $this->slug,
            'title'           => $this->title,
            'caption'         => $this->caption,
            'cover_image_url' => $this->cover_image,
            'type'            => $this->type,
            'is_ranked'       => $this->is_ranked,
            'is_public'       => $this->is_public,
            'status'          => $this->status,
            'items_count'     => $this->items_count,
            'loves_count'     => $this->loves_count,
            'comments_count'  => $this->comments_count,
            'views_count'     => $this->views_count ?? 0,
            'is_loved'        => Auth::check() ? $this->isLovedBy(Auth::id()) : false,
            'is_mine'         => Auth::id() === $this->user_id,
            'published_at'    => $this->published_at?->toISOString(),
            'created_at'      => $this->created_at->toISOString(),
            'user'            => [
                'id'           => $this->user->id,
                'username'     => $this->user->profile?->username,
                'display_name' => $this->user->profile?->display_name,
                'avatar'       => $this->user->profile?->avatar,
            ],
            'vibetags'        => VibetagResource::collection($this->whenLoaded('vibetags')),
            'items'           => RotationItemResource::collection($this->whenLoaded('items')),
            'contains_item'   => $this->when($this->resource->getAttribute('contains_item') !== null, (bool) $this->resource->getAttribute('contains_item')),
        ];
    }
}
