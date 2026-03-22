<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total      = User::count();
        $onboarded  = User::whereNotNull('onboarding_completed_at')->count();
        $newThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
        $newToday   = User::where('created_at', '>=', now()->startOfDay())->count();

        return [
            Stat::make('Total Users', number_format($total))
                ->description("{$newToday} joined today")
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Onboarded', number_format($onboarded))
                ->description(number_format($total > 0 ? round($onboarded / $total * 100) : 0) . '% completion rate')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('New This Week', number_format($newThisWeek))
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),
        ];
    }
}
