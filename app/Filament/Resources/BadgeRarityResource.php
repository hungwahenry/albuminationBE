<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeRarityResource\Pages;
use App\Models\BadgeRarityConfig;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class BadgeRarityResource extends Resource
{
    protected static ?string $model = BadgeRarityConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationLabel = 'Rarities';
    protected static ?string $navigationGroup = 'Features';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Badge Rarity';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->schema([
                Grid::make(2)->schema([
                    TextInput::make('key')
                        ->disabled()
                        ->helperText('Rarity keys are fixed and match the badge rarity enum.'),

                    TextInput::make('label')
                        ->required()
                        ->helperText('Display name shown in the app.'),
                ]),
            ]),

            Section::make('Colors')->schema([
                Grid::make(3)->schema([
                    ColorPicker::make('color')
                        ->label('Primary Color')
                        ->helperText('Used for text, borders, and dots.')
                        ->required(),

                    ColorPicker::make('bg_color')
                        ->label('Background Color (Solid)')
                        ->helperText('Used for filled circles and icon backgrounds.')
                        ->required(),

                    ColorPicker::make('bg_light_color')
                        ->label('Background Color (Light)')
                        ->helperText('Used for chip backgrounds and rarity pill fills.')
                        ->required(),
                ]),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Identity')->schema([
                InfoGrid::make(3)->schema([
                    TextEntry::make('key')->badge()->color('gray'),
                    TextEntry::make('label'),
                    TextEntry::make('sort_order')->label('Sort Order'),
                ]),
            ]),

            InfoSection::make('Colors')->schema([
                InfoGrid::make(3)->schema([
                    ColorEntry::make('color')->label('Primary Color')->copyable(),
                    ColorEntry::make('bg_color')->label('Solid Background')->copyable(),
                    ColorEntry::make('bg_light_color')->label('Light Background')->copyable(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('key')->badge()->color('gray'),
                TextColumn::make('label')->sortable(),
                ColorColumn::make('color')->label('Primary'),
                ColorColumn::make('bg_color')->label('Solid BG'),
                ColorColumn::make('bg_light_color')->label('Light BG'),
                TextColumn::make('badges_count')
                    ->label('Badges')
                    ->state(fn (BadgeRarityConfig $r) => $r->badges()->count()),
            ])
            ->defaultSort('sort_order')
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->after(function (BadgeRarityConfig $record) {
                        // Bust rarity cache so API reflects new colors immediately
                        Cache::forget("badge_rarity:{$record->key}");
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Updated rarity config: {$record->key}");
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadgeRarities::route('/'),
            'view'  => Pages\ViewBadgeRarity::route('/{record}'),
            'edit'  => Pages\EditBadgeRarity::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Rarities are fixed — managed via seeder, not created manually
    }
}
