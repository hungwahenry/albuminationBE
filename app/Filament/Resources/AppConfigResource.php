<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppConfigResource\Pages;
use App\Models\AppConfig;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppConfigResource extends Resource
{
    protected static ?string $model = AppConfig::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'App Config';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'maintenance'  => 'danger',
                        'registration' => 'warning',
                        'features'     => 'success',
                        'versions'     => 'info',
                        'limits'       => 'gray',
                        default        => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('key')->searchable()->copyable()->fontFamily('mono'),
                TextColumn::make('value')
                    ->formatStateUsing(fn (AppConfig $r) => $r->type === 'boolean'
                        ? ($r->cast_value ? 'true' : 'false')
                        : ($r->value ?? '—')
                    )
                    ->badge()
                    ->color(fn (AppConfig $r) => $r->type === 'boolean'
                        ? ($r->cast_value ? 'success' : 'danger')
                        : 'gray'
                    ),
                TextColumn::make('type')->badge()->color('gray'),
                TextColumn::make('description')->limit(70)->wrap(),
            ])
            ->defaultSort('group')
            ->filters([
                SelectFilter::make('group')
                    ->options([
                        'maintenance'  => 'Maintenance',
                        'registration' => 'Registration',
                        'features'     => 'Features',
                        'versions'     => 'Versions',
                        'limits'       => 'Limits',
                    ]),
            ])
            ->actions([
                // Boolean: inline toggle
                Action::make('toggle')
                    ->label(fn (AppConfig $r) => $r->cast_value ? 'Disable' : 'Enable')
                    ->icon(fn (AppConfig $r) => $r->cast_value ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (AppConfig $r) => $r->cast_value ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn (AppConfig $r) => $r->type === 'boolean' && auth()->user()->can('app_config.manage'))
                    ->action(function (AppConfig $record) {
                        $newValue = $record->cast_value ? 'false' : 'true';
                        $record->update(['value' => $newValue]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Set {$record->key} = {$newValue}");
                        Notification::make()
                            ->title("{$record->key} set to {$newValue}")
                            ->success()->send();
                    }),

                // String / integer: edit modal
                EditAction::make()
                    ->visible(fn (AppConfig $r) => $r->type !== 'boolean' && auth()->user()->can('app_config.manage'))
                    ->form(fn (AppConfig $r) => $r->type === 'integer'
                        ? [
                            TextInput::make('value')
                                ->label($r->key)
                                ->helperText($r->description)
                                ->integer()
                                ->required(),
                        ]
                        : [
                            Textarea::make('value')
                                ->label($r->key)
                                ->helperText($r->description)
                                ->rows(3)
                                ->required(),
                        ]
                    )
                    ->using(function (AppConfig $record, array $data) {
                        $record->update(['value' => $data['value']]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Updated {$record->key} = {$data['value']}");
                        return $record;
                    })
                    ->successNotificationTitle(fn (AppConfig $r) => "{$r->key} updated"),
            ])
            ->bulkActions([])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppConfigs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
