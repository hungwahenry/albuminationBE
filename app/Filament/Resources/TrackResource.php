<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackResource\Pages;
use App\Models\Track;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;
    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Track')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('position')->label('#'),
                    TextEntry::make('title'),
                    TextEntry::make('length')->label('Duration')
                        ->state(fn (Track $t) => static::formatLength($t->length))
                        ->placeholder('—'),
                    TextEntry::make('favourites_count')->label('Favourites'),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('album.title')->label('Album'),
                    TextEntry::make('artists_string')
                        ->label('Artists')
                        ->state(fn (Track $t) => $t->artists->map(fn ($a) => $a->name)->implode(', '))
                        ->placeholder('—'),
                ]),
                TextEntry::make('mbid')->label('MBID'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')->label('#')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('album.title')->label('Album')->searchable()->sortable(),
                TextColumn::make('artists_string')
                    ->label('Artists')
                    ->state(fn (Track $t) => $t->artists->map(fn ($a) => $a->name)->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('length')->label('Duration')
                    ->state(fn (Track $t) => static::formatLength($t->length))
                    ->placeholder('—'),
                TextColumn::make('favourites_count')->label('Favourites')->sortable(),
            ])
            ->defaultSort('album_id')
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['album', 'artists']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTracks::route('/'),
            'view'  => Pages\ViewTrack::route('/{record}'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }

    public static function formatLength(?int $ms): string
    {
        if ($ms === null) return '—';
        $seconds = intdiv($ms, 1000);
        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
