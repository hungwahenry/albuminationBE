<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EnrichArtistJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 30;
    public array $backoff = [10, 30];

    public function __construct(public readonly int $artistId) {}

    public function handle(MusicBrainzService $musicBrainz): void
    {
        $artist = Artist::find($this->artistId);

        if (!$artist || !$artist->mbid) {
            return;
        }

        $musicBrainz->fetchArtist($artist->mbid);
    }
}
