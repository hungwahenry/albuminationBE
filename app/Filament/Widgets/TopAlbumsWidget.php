<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopAlbumsWidget extends TableWidget
{
    protected static ?string $heading = 'Top Albums by Engagement';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Album::with('artists')->orderByDesc('takes_count')->limit(10))
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('artists_string')
                    ->label('Artists')
                    ->state(fn (Album $a) => $a->artists->map(fn ($ar) => $ar->name)->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('takes_count')->label('Takes')->sortable(),
                TextColumn::make('loves_count')->label('Loves')->sortable(),
                TextColumn::make('hits_count')->label('Hits')->sortable(),
                TextColumn::make('misses_count')->label('Misses')->sortable(),
            ])
            ->paginated(false);
    }
}
