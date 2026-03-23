<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CatalogHealthWidget;
use App\Filament\Widgets\CatalogStatsWidget;
use App\Filament\Widgets\ContentStatsWidget;
use App\Filament\Widgets\EngagementChartWidget;
use App\Filament\Widgets\ModerationStatsWidget;
use App\Filament\Widgets\TopAlbumsWidget;
use App\Filament\Widgets\UserGrowthChartWidget;
use App\Filament\Widgets\UserStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            UserStatsWidget::class,
            ContentStatsWidget::class,
            ModerationStatsWidget::class,
            UserGrowthChartWidget::class,
            TopAlbumsWidget::class,
            CatalogStatsWidget::class,
            CatalogHealthWidget::class,
            EngagementChartWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 3;
    }
}
