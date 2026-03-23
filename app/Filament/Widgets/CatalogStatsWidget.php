<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected function getStats(): array
    {
        $albums  = Album::count();
        $artists = Artist::count();
        $tracks  = Track::count();

        $newAlbumsThisWeek = Album::where('created_at', '>=', now()->subWeek())->count();

        return [
            Stat::make('Albums', number_format($albums))
                ->description("{$newAlbumsThisWeek} added this week")
                ->icon('heroicon-o-musical-note')
                ->color('primary'),

            Stat::make('Artists', number_format($artists))
                ->icon('heroicon-o-microphone')
                ->color('info'),

            Stat::make('Tracks', number_format($tracks))
                ->icon('heroicon-o-queue-list')
                ->color('warning'),
        ];
    }
}
