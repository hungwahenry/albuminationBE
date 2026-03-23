<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use App\Models\Artist;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $stubArtists    = Artist::whereNull('type')->count();
        $unsyncedArtists = Artist::whereNull('albums_synced_at')->whereNotNull('mbid')->count();
        $albumsNoTracks = Album::whereDoesntHave('tracks')->count();

        return [
            Stat::make('Stub Artists', number_format($stubArtists))
                ->description('Missing type/metadata — use Enrich action')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($stubArtists > 0 ? 'danger' : 'success'),

            Stat::make('Artists Never Synced', number_format($unsyncedArtists))
                ->description('Discography not yet fetched')
                ->icon('heroicon-o-arrow-path')
                ->color($unsyncedArtists > 0 ? 'warning' : 'success'),

            Stat::make('Albums Without Tracks', number_format($albumsNoTracks))
                ->description('Missing tracks — use Seed Tracks action')
                ->icon('heroicon-o-exclamation-circle')
                ->color($albumsNoTracks > 0 ? 'danger' : 'success'),
        ];
    }
}
