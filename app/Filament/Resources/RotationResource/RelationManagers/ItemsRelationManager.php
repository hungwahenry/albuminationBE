<?php

namespace App\Filament\Resources\RotationResource\RelationManagers;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\TrackResource;
use App\Models\RotationItem;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('position')
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('album.title')->label('Album')->searchable(),
                TextColumn::make('track.title')->label('Track')->placeholder('—'),
            ])
            ->defaultSort('position')
            ->actions([
                Action::make('view_album')
                    ->label('Album')
                    ->icon('heroicon-o-musical-note')
                    ->visible(fn (RotationItem $record) => $record->album_id !== null)
                    ->url(fn (RotationItem $record) => $record->album_id
                        ? AlbumResource::getUrl('view', ['record' => $record->album_id])
                        : null),
                Action::make('view_track')
                    ->label('Track')
                    ->icon('heroicon-o-play')
                    ->visible(fn (RotationItem $record) => $record->track_id !== null)
                    ->url(fn (RotationItem $record) => $record->track_id
                        ? TrackResource::getUrl('view', ['record' => $record->track_id])
                        : null),
            ])
            ->bulkActions([])
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->with(['album', 'track']));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
