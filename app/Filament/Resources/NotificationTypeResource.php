<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTypeResource\Pages;
use App\Models\NotificationType;
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

class NotificationTypeResource extends Resource
{
    protected static ?string $model = NotificationType::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Identity')->schema([
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Snake_case identifier used in code, e.g. new_follower')
                    ->alphaDash(),
                TextInput::make('label')->required(),
                TextInput::make('description')->required(),
            ])->columns(1),

            Section::make('Display')->schema([
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->integer()
                    ->required()
                    ->default(fn () => (NotificationType::max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')->label('Active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('key')->searchable()->copyable(),
                TextColumn::make('label')->searchable(),
                TextColumn::make('description')->limit(60)->placeholder('—'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Action::make('toggle_active')
                    ->label(fn (NotificationType $r) => $r->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (NotificationType $r) => $r->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (NotificationType $r) => $r->is_active ? 'warning' : 'success')
                    ->visible(fn () => auth('admin')->user()?->can('notifications.manage'))
                    ->action(function (NotificationType $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log($record->is_active ? 'Activated notification type' : 'Deactivated notification type');
                        Notification::make()
                            ->title('Notification type ' . ($record->is_active ? 'activated' : 'deactivated'))
                            ->success()->send();
                    }),
                EditAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('notifications.manage')),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotificationTypes::route('/'),
            'create' => Pages\CreateNotificationType::route('/create'),
            'edit'   => Pages\EditNotificationType::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth('admin')->user()?->can('notifications.manage');
    }
}
