<?php

namespace App\Filament\Widgets;

use App\Models\Follow;
use App\Models\Love;
use App\Models\Rotation;
use App\Models\RotationComment;
use App\Models\Take;
use App\Models\TakeReply;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Takes', number_format(Take::where('is_deleted', false)->count()))
                ->description(number_format(Take::where('rating', 'hit')->count()) . ' hits · ' . number_format(Take::where('rating', 'miss')->count()) . ' misses')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),

            Stat::make('Rotations', number_format(Rotation::where('status', 'published')->count()))
                ->description(number_format(Rotation::where('status', 'draft')->count()) . ' drafts')
                ->icon('heroicon-o-queue-list')
                ->color('primary'),

            Stat::make('Comments & Replies', number_format(
                RotationComment::where('is_deleted', false)->count() +
                TakeReply::where('is_deleted', false)->count()
            ))
                ->icon('heroicon-o-chat-bubble-oval-left')
                ->color('info'),

            Stat::make('Loves', number_format(Love::count()))
                ->description(number_format(Follow::count()) . ' follows')
                ->icon('heroicon-o-heart')
                ->color('danger'),
        ];
    }
}
