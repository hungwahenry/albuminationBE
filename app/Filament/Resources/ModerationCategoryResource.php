<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationCategoryResource\Pages;
use App\Models\ModerationCategory;
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

class ModerationCategoryResource extends Resource
{
    protected static ?string $model = ModerationCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?string $navigationLabel = 'Moderation Thresholds';
    protected static ?string $slug = 'moderation-thresholds';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('OpenAI category')
                ->helperText('Must exactly match an OpenAI moderation category (e.g. "hate/threatening"). Changing this breaks scoring — only edit if you know what you are doing.')
                ->disabled(fn (?ModerationCategory $r) => $r !== null)
                ->dehydrated()
                ->required(),

            TextInput::make('label')
                ->label('Display label')
                ->required(),

            TextInput::make('threshold')
                ->label('Threshold (0.00 = always block, 1.00 = never block)')
                ->helperText('Content with a score ≥ this value will be blocked. Lower = stricter.')
                ->numeric()
                ->step(0.01)
                ->minValue(0)
                ->maxValue(1)
                ->required(),

            Toggle::make('enabled')
                ->label('Active')
                ->helperText('When off, this category will not block content even if OpenAI flags it.')
                ->default(true),

            TextInput::make('sort_order')
                ->integer()
                ->default(fn () => (ModerationCategory::max('sort_order') ?? 0) + 1)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('label')->searchable()->sortable(),
                TextColumn::make('name')->label('OpenAI category')->fontFamily('mono')->copyable(),
                TextColumn::make('threshold')
                    ->badge()
                    ->color(fn (ModerationCategory $r) => $r->threshold <= 0.30 ? 'danger' : ($r->threshold <= 0.50 ? 'warning' : 'gray'))
                    ->formatStateUsing(fn (float $state) => number_format($state, 2)),
                IconColumn::make('enabled')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Action::make('toggle')
                    ->label(fn (ModerationCategory $r) => $r->enabled ? 'Disable' : 'Enable')
                    ->icon(fn (ModerationCategory $r) => $r->enabled ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (ModerationCategory $r) => $r->enabled ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn () => auth('admin')->user()?->can('moderation.manage'))
                    ->action(function (ModerationCategory $record) {
                        $record->update(['enabled' => !$record->enabled]);
                        activity()->causedBy(auth('admin')->user())->performedOn($record)
                            ->log(($record->enabled ? 'Enabled' : 'Disabled') . " moderation category: {$record->name}");
                        Notification::make()
                            ->title("{$record->label} " . ($record->enabled ? 'enabled' : 'disabled'))
                            ->success()->send();
                    }),
                EditAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('moderation.manage'))
                    ->after(function (ModerationCategory $record) {
                        activity()->causedBy(auth('admin')->user())->performedOn($record)
                            ->withProperties(['changes' => $record->getChanges()])
                            ->log("Updated moderation category: {$record->name}");
                    }),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModerationCategories::route('/'),
            'edit'  => Pages\EditModerationCategory::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Categories are pinned to OpenAI's category list; admins should only tune existing rows.
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth('admin')->user()?->can('moderation.manage') ?? false;
    }
}
