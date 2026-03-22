<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ModerationStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $pending   = Report::where('status', 'pending')->count();
        $reviewed  = Report::where('status', 'reviewed')->count();
        $actioned  = Report::where('status', 'actioned')->count();
        $dismissed = Report::where('status', 'dismissed')->count();

        return [
            Stat::make('Pending Reports', number_format($pending))
                ->description('Requires action')
                ->icon('heroicon-o-flag')
                ->color($pending > 0 ? 'danger' : 'success'),

            Stat::make('Awaiting Review', number_format($reviewed))
                ->icon('heroicon-o-eye')
                ->color('warning'),

            Stat::make('Actioned', number_format($actioned))
                ->description(number_format($dismissed) . ' dismissed')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
