<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class MostBlockedUsersWidget extends TableWidget
{
    protected static ?string $heading = 'Most Blocked Users (last 7 days)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $cutoff = now()->subDays(7);

        return $table
            ->query(
                User::query()
                    ->select('users.*')
                    ->selectSub(
                        Activity::query()
                            ->selectRaw('COUNT(DISTINCT causer_id)')
                            ->where('log_name', 'moderation')
                            ->where('subject_type', User::class)
                            ->whereColumn('subject_id', 'users.id')
                            ->where('created_at', '>=', $cutoff),
                        'recent_block_count'
                    )
                    ->with('profile')
                    ->having('recent_block_count', '>=', 1)
                    ->orderByDesc('recent_block_count')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('profile.username')->label('Username')->placeholder('—'),
                TextColumn::make('email')->placeholder('—'),
                TextColumn::make('recent_block_count')
                    ->label('Unique blockers (7d)')
                    ->badge()
                    ->color(fn ($state) => $state >= 5 ? 'danger' : ($state >= 3 ? 'warning' : 'gray')),
                TextColumn::make('created_at')->label('Joined')->dateTime()->since(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Review')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (User $user) => UserResource::getUrl('view', ['record' => $user->id])),
            ])
            ->emptyStateHeading('No users blocked in the last 7 days')
            ->paginated(false);
    }
}
