<?php

namespace App\Filament\Resources\BadgeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EarnedByRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $title = 'Earned By';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile.avatar_url')
                    ->label('')
                    ->size(32)
                    ->circular()
                    ->defaultImageUrl(fn () => null),

                TextColumn::make('name')->searchable()->sortable(),

                TextColumn::make('profile.username')
                    ->label('Username')
                    ->prefix('@')
                    ->searchable(),

                TextColumn::make('pivot.earned_at')
                    ->label('Earned At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('pivot.earned_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
