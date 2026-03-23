<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Jobs\EnrichArtistJob;
use App\Models\Artist;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                ->helperText('Paste an external URL, or upload a file below.')
                ->columnSpanFull(),

            FileUpload::make('image_file')
                ->label('Upload Image')
                ->disk('public')
                ->directory('artists')
                ->image()
                ->imagePreviewHeight('160')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(4096)
                ->nullable()
                ->helperText('Uploading a file will replace the URL above.')
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
                    ImageEntry::make('image_url')
                        ->label('Preview')
                        ->height(160)
                        ->visible(fn (Artist $record) => filled($record->image_url)),
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
                ImageColumn::make('image_url')
                    ->label('')
                    ->size(48)
                    ->circular()
                    ->defaultImageUrl(fn () => null),
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
            ->filters([
                Filter::make('stubs')
                    ->label('Stubs (missing metadata)')
                    ->query(fn (Builder $query) => $query->whereNull('type')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->label('Edit Image')
                    ->visible(fn () => auth('admin')->user()?->can('catalog.edit')),
                Action::make('enrich')
                    ->label('Enrich from MB')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Artist $record) => $record->mbid && auth('admin')->user()?->can('catalog.sync'))
                    ->action(function (Artist $artist) {
                        EnrichArtistJob::dispatch($artist->id);
                        activity()->causedBy(auth()->user())->performedOn($artist)
                            ->log("Queued MusicBrainz enrichment for artist: {$artist->name}");
                        Notification::make()->title("Enrichment queued for {$artist->name}")->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('enrich')
                    ->label('Enrich from MusicBrainz')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn () => auth('admin')->user()?->can('catalog.sync'))
                    ->requiresConfirmation()
                    ->modalDescription('This will queue a MusicBrainz enrichment job for each selected artist to fill missing metadata.')
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $artist) {
                            if ($artist->mbid) {
                                EnrichArtistJob::dispatch($artist->id);
                                $count++;
                            }
                        }
                        activity()->causedBy(auth()->user())->log("Queued MusicBrainz enrichment for {$count} artists");
                        Notification::make()->title("Enrichment queued for {$count} artists")->success()->send();
                    }),
            ]);
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
