<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a Rotation model (with loaded vibetags and items) for data export.
 * Excludes all auth-derived UI state (is_loved, is_mine, contains_item).
 */
class ExportRotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'caption'        => $this->caption,
            'type'           => $this->type,
            'is_ranked'      => $this->is_ranked,
            'is_public'      => $this->is_public,
            'status'         => $this->status,
            'loves_count'    => $this->loves_count,
            'comments_count' => $this->comments_count,
            'published_at'   => $this->published_at?->toISOString(),
            'created_at'     => $this->created_at->toISOString(),
            'vibetags'       => $this->vibetags->pluck('name')->all(),
            'items'          => ExportRotationItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
