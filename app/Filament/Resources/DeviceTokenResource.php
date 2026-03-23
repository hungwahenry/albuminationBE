<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceTokenResource\Pages;
use App\Models\DeviceToken;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DeviceTokenResource extends Resource
{
    protected static ?string $model = DeviceToken::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationGroup = 'Users';
    protected static ?string $navigationLabel = 'Device Tokens';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')
                    ->label('Username')
                    ->searchable()
                    ->url(fn (DeviceToken $r) => route('filament.admin.resources.users.view', $r->user_id)),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state) => match (strtolower($state)) {
                        'ios'     => 'info',
                        'android' => 'success',
                        default   => 'gray',
                    }),
                TextColumn::make('expo_version')->label('Expo Version')->placeholder('—'),
                TextColumn::make('token')
                    ->label('Token')
                    ->limit(24)
                    ->copyable()
                    ->tooltip(fn (DeviceToken $r) => $r->token),
                TextColumn::make('last_used_at')
                    ->label('Last Used')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                TextColumn::make('created_at')->label('Registered')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('platform')
                    ->options(['ios' => 'iOS', 'android' => 'Android']),
                Filter::make('stale')
                    ->label('Stale (90+ days unused)')
                    ->query(fn (Builder $query) => $query->where(function ($q) {
                        $q->whereNull('last_used_at')
                          ->orWhere('last_used_at', '<', now()->subDays(90));
                    })->where('created_at', '<', now()->subDays(90))),
            ])
            ->actions([
                DeleteAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('device_tokens.manage'))
                    ->before(fn (DeviceToken $r) => activity()->causedBy(auth()->user())
                        ->performedOn($r)->log("Deleted device token for user ID {$r->user_id}")),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('prune_stale')
                        ->label('Prune Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => auth('admin')->user()?->can('device_tokens.manage'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->delete();
                            activity()->causedBy(auth()->user())
                                ->log("Pruned {$count} device tokens");
                            Notification::make()->title("{$count} device tokens removed")->success()->send();
                        }),
                    DeleteBulkAction::make()
                        ->visible(fn () => auth('admin')->user()?->can('device_tokens.manage')),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceTokens::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
