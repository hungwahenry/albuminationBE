<?php

namespace App\Jobs;

use App\Models\Artist;
use App\Services\MusicBrainz\MusicBrainzService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncArtistAlbumsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [15, 60];

    public function __construct(public readonly int $artistId) {}

    public function handle(MusicBrainzService $musicBrainz): void
    {
        $artist = Artist::find($this->artistId);

        if (!$artist || !$artist->mbid) {
            return;
        }

        $musicBrainz->fetchArtistAlbums($artist);
        $artist->update(['albums_synced_at' => now()]);
    }
}
