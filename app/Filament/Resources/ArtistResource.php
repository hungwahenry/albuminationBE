<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Models\Artist;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;
    protected static ?string $navigationIcon = 'heroicon-o-microphone';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('image_url')
                ->label('Image URL')
                ->url()
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Artist')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('name'),
                    TextEntry::make('type')->placeholder('—'),
                    TextEntry::make('country')->placeholder('—'),
                    TextEntry::make('mbid')->label('MBID'),
                ]),
                Grid::make(3)->schema([
                    TextEntry::make('disambiguation')->placeholder('—'),
                    TextEntry::make('begin_date')->label('Begin Date')->date()->placeholder('—'),
                    TextEntry::make('end_date')->label('End Date')->date()->placeholder('—'),
                ]),
                Grid::make(1)->schema([
                    TextEntry::make('image_url')->label('Image URL')->placeholder('—')->copyable(),
                ]),
            ]),

            Section::make('Catalog')->schema([
                Grid::make(2)->schema([
                    TextEntry::make('albums_count')
                        ->label('Albums in Catalog')
                        ->state(fn (Artist $a) => $a->albums()->count()),
                    TextEntry::make('tracks_count')
                        ->label('Tracks in Catalog')
                        ->state(fn (Artist $a) => $a->tracks()->count()),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->placeholder('—'),
                TextColumn::make('country')->placeholder('—'),
                TextColumn::make('disambiguation')->placeholder('—')->limit(40),
                TextColumn::make('albums_count')
                    ->label('Albums')
                    ->state(fn (Artist $a) => $a->albums()->count())
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Edit Image')
                    ->visible(fn () => auth()->user()->can('catalog.edit')),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtists::route('/'),
            'view'  => Pages\ViewArtist::route('/{record}'),
            'edit'  => Pages\EditArtist::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
