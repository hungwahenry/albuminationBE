<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Models\Album;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;
    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Album')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('title'),
                    TextEntry::make('type'),
                    TextEntry::make('release_date')->date(),
                    TextEntry::make('mbid')->label('MBID'),
                ]),
                Grid::make(1)->schema([
                    TextEntry::make('artists_string')
                        ->label('Artists')
                        ->state(fn (Album $a) => $a->artists->map(fn ($ar) => $ar->name)->implode(', '))
                        ->placeholder('—'),
                ]),
            ]),

            Section::make('Engagement')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('takes_count')->label('Takes'),
                    TextEntry::make('loves_count')->label('Loves'),
                    TextEntry::make('hits_count')->label('Hits'),
                    TextEntry::make('misses_count')->label('Misses'),
                ]),
            ]),

            Section::make('Catalog Stats')->schema([
                Grid::make(2)->schema([
                    TextEntry::make('tracks_count')
                        ->label('Tracks in Catalog')
                        ->state(fn (Album $a) => $a->tracks()->count()),
                    TextEntry::make('created_at')->label('Added to Catalog')->dateTime(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('artists_string')
                    ->label('Artists')
                    ->state(fn (Album $a) => $a->artists->map(fn ($ar) => $ar->name)->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('type'),
                TextColumn::make('release_date')->date()->sortable(),
                TextColumn::make('takes_count')->label('Takes')->sortable(),
                TextColumn::make('loves_count')->label('Loves')->sortable(),
            ])
            ->defaultSort('takes_count', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(['Album' => 'Album', 'EP' => 'EP']),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('flush_cache')
                    ->label('Flush Cache')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('This will clear the MusicBrainz cache for this album\'s MBID. The next search will re-fetch from MusicBrainz.')
                    ->visible(fn () => auth()->user()->can('catalog.cache.flush'))
                    ->action(function (Album $album) {
                        foreach ([5, 10, 15, 25, 50] as $limit) {
                            Cache::forget("mb_search:album:" . md5("{$album->title}:{$limit}"));
                            Cache::forget("mb_search:album:" . md5("{$album->title}:{$limit}") . ':fresh');
                        }
                        activity()->causedBy(auth()->user())->performedOn($album)
                            ->log("Flushed MusicBrainz cache for album: {$album->title}");
                        Notification::make()->title('Cache flushed for ' . $album->title)->success()->send();
                    }),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn ($q) => $q->with('artists'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlbums::route('/'),
            'view'  => Pages\ViewAlbum::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
