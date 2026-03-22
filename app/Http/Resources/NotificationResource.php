<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $raw = $this->data ?? [];

        $groupingKeys = ['group_key', 'count', 'actors', 'actor', 'latest_at'];
        $data = array_diff_key($raw, array_flip($groupingKeys));

        return [
            'id'         => $this->id,
            'type'       => $raw['type'] ?? class_basename($this->type),
            'count'      => $raw['count'] ?? 1,
            'actors'     => $raw['actors'] ?? array_filter([($raw['actor'] ?? null)]),
            'latest_at'  => $raw['latest_at'] ?? $this->created_at->toISOString(),
            'data'       => $data,
            'read_at'    => $this->read_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
