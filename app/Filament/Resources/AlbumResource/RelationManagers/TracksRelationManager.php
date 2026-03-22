<?php

namespace App\Filament\Resources\AlbumResource\RelationManagers;

use App\Filament\Resources\TrackResource;
use App\Models\Track;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TracksRelationManager extends RelationManager
{
    protected static string $relationship = 'tracks';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('artists_string')
                    ->label('Artists')
                    ->state(fn (Track $t) => $t->artists->map(fn ($a) => $a->name)->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('length')->label('Duration')
                    ->state(fn (Track $t) => TrackResource::formatLength($t->length))
                    ->placeholder('—'),
                TextColumn::make('favourites_count')->label('Favourites')->sortable(),
            ])
            ->defaultSort('position')
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Track $record) => TrackResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([])
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->with('artists'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
