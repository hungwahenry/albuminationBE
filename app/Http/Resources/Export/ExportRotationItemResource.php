<?php

namespace App\Http\Resources\Export;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes a RotationItem model (with loaded album/track/artists) for data export.
 */
class ExportRotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = ['position' => $this->position];

        if ($this->album) {
            $data['album'] = [
                'mbid'         => $this->album->mbid,
                'title'        => $this->album->title,
                'type'         => $this->album->type,
                'release_date' => $this->album->release_date?->toDateString(),
                'artists'      => $this->album->artists->map(fn ($a) => [
                    'name'        => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ];
        }

        if ($this->track) {
            $data['track'] = [
                'mbid'    => $this->track->mbid,
                'title'   => $this->track->title,
                'length'  => $this->track->length,
                'album'   => $this->track->album ? [
                    'mbid'  => $this->track->album->mbid,
                    'title' => $this->track->album->title,
                ] : null,
                'artists' => $this->track->artists->map(fn ($a) => [
                    'name'        => $a->name,
                    'join_phrase' => $a->pivot->join_phrase,
                ])->all(),
            ];
        }

        return $data;
    }
}
