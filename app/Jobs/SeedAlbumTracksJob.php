<?php

namespace App\Jobs;

use App\Models\Album;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SeedAlbumTracksJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [15, 60];

    public function __construct(public readonly int $albumId) {}

    public function handle(MusicBrainzService $musicBrainz): void
    {
        $album = Album::find($this->albumId);

        if (!$album || !$album->mbid) {
            return;
        }

        $musicBrainz->fetchAlbumTracks($album);
    }
}
