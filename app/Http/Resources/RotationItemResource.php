<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = [
            'id'       => $this->id,
            'position' => $this->position,
        ];

        if ($this->album) {
            $base['album'] = [
                'id'            => $this->album->id,
                'mbid'          => $this->album->mbid,
                'title'         => $this->album->title,
                'type'          => $this->album->type,
                'release_date'  => $this->album->release_date?->toDateString(),
                'cover_art_url' => $this->album->cover_art_url,
                'artists'       => $this->album->artists->map(fn ($a) => [
                    'mbid'        => $a->mbid,
                    'name'        => $a->name,
                    'join_phrase'  => $a->pivot->join_phrase,
                ])->all(),
            ];
        }

        if ($this->track) {
            $base['track'] = [
                'id'       => $this->track->id,
                'mbid'     => $this->track->mbid,
                'title'    => $this->track->title,
                'length'   => $this->track->length,
                'album'    => $this->track->album ? [
                    'mbid'          => $this->track->album->mbid,
                    'title'         => $this->track->album->title,
                    'cover_art_url' => $this->track->album->cover_art_url,
                ] : null,
                'artists'  => $this->track->artists->map(fn ($a) => [
                    'mbid'        => $a->mbid,
                    'name'        => $a->name,
                    'join_phrase'  => $a->pivot->join_phrase,
                ])->all(),
            ];
        }

        return $base;
    }
}
