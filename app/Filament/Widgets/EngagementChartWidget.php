<?php

namespace App\Filament\Widgets;

use App\Models\Love;
use App\Models\Take;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EngagementChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Engagement (Last 30 Days)';
    protected static ?int $sort = 8;
    protected static ?string $maxHeight = '250px';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->startOfDay());

        $takes = Take::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('is_deleted', false)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $loves = Love::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = $days->map(fn (Carbon $d) => $d->format('M j'))->values()->all();

        return [
            'datasets' => [
                [
                    'label'           => 'Takes',
                    'data'            => $days->map(fn (Carbon $d) => $takes[$d->toDateString()] ?? 0)->values()->all(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Loves',
                    'data'            => $days->map(fn (Carbon $d) => $loves[$d->toDateString()] ?? 0)->values()->all(),
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
