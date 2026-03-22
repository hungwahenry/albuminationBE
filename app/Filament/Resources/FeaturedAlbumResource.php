<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedAlbumResource\Pages;
use App\Models\Album;
use App\Models\FeaturedAlbum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeaturedAlbumResource extends Resource
{
    protected static ?string $model = FeaturedAlbum::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('album_id')
                ->label('Album')
                ->options(Album::orderBy('title')->pluck('title', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('sort_order')
                ->label('Position')
                ->integer()
                ->required()
                ->default(FeaturedAlbum::max('sort_order') + 1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('album.title')->label('Album')->searchable(),
                TextColumn::make('album_artists')
                    ->label('Artists')
                    ->state(fn (FeaturedAlbum $r) => $r->album->artists->map(fn ($a) => $a->name)->implode(', '))
                    ->placeholder('—'),
                TextColumn::make('album.type')->label('Type'),
                TextColumn::make('album.takes_count')->label('Takes'),
                TextColumn::make('album.loves_count')->label('Loves'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make()
                    ->visible(fn () => auth()->user()->can('catalog.featured.manage')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('catalog.featured.manage')),
            ])
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with(['album.artists']));
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeaturedAlbums::route('/'),
            'create' => Pages\CreateFeaturedAlbum::route('/create'),
            'edit'   => Pages\EditFeaturedAlbum::route('/{record}/edit'),
        ];
    }
}
