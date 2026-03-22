<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a Take model (with loaded album and artists) for data export.
 * Excludes all auth-derived UI state (can_edit, is_mine, user_reaction).
 * Soft-deleted takes preserve their tombstone rather than being silently omitted.
 */
class ExportTakeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'rating'          => $this->is_deleted ? null : $this->rating,
            'body'            => $this->is_deleted ? null : $this->body,
            'is_deleted'      => $this->is_deleted,
            'is_edited'       => $this->edited_at !== null,
            'agrees_count'    => $this->agrees_count,
            'disagrees_count' => $this->disagrees_count,
            'replies_count'   => $this->replies_count,
            'created_at'      => $this->created_at->toISOString(),
            'album'           => $this->when($this->relationLoaded('album') && $this->album, fn () => [
                'mbid'         => $this->album->mbid,
                'title'        => $this->album->title,
                'release_date' => $this->album->release_date?->toDateString(),
                'artists'      => $this->album->artists
                    ->map(fn ($a) => $a->name . ($a->pivot->join_phrase ?? ''))
                    ->join(''),
            ]),
        ];
    }
}
