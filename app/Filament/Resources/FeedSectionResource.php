<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedSectionResource\Pages;
use App\Models\FeedSection;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedSectionResource extends Resource
{
    protected static ?string $model = FeedSection::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Display')->schema([
                TextInput::make('title')->required(),
                TextInput::make('subtitle')->nullable(),
                TextInput::make('sort_order')->integer()->required(),
            ])->columns(3),

            Section::make('Visibility Rules')->schema([
                Toggle::make('is_active')->label('Active'),
                Toggle::make('requires_follows')->label('Requires Follows'),
                TextInput::make('min_account_age_days')
                    ->label('Min Account Age (days)')
                    ->integer()
                    ->default(0),
            ])->columns(3),

            Section::make('Config')->schema([
                KeyValue::make('config')
                    ->label('Section Config (JSON)')
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('type')->searchable(),
                TextColumn::make('title')->searchable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('requires_follows')->label('Needs Follows')->boolean(),
                TextColumn::make('min_account_age_days')->label('Min Age (days)'),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Action::make('toggle_active')
                    ->label(fn (FeedSection $r) => $r->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (FeedSection $r) => $r->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (FeedSection $r) => $r->is_active ? 'warning' : 'success')
                    ->visible(fn () => auth()->user()->can('feed.manage'))
                    ->action(function (FeedSection $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log($record->is_active ? 'Activated feed section' : 'Deactivated feed section');
                        Notification::make()
                            ->title('Feed section ' . ($record->is_active ? 'activated' : 'deactivated'))
                            ->success()->send();
                    }),
                EditAction::make()
                    ->visible(fn () => auth()->user()->can('feed.manage')),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedSections::route('/'),
            'edit'  => Pages\EditFeedSection::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
