<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UserGrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'User Growth (Last 30 Days)';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $days   = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());
        $counts = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label'           => 'New Users',
                    'data'            => $days->map(fn (Carbon $d) => $counts[$d->toDateString()] ?? 0)->values()->all(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $days->map(fn (Carbon $d) => $d->format('M j'))->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
