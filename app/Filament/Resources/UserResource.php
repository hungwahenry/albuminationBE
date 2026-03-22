<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\ExportService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),
            DateTimePicker::make('email_verified_at')->nullable(),
            DateTimePicker::make('onboarding_completed_at')->nullable(),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Account')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('email'),
                    TextEntry::make('created_at')->label('Joined')->dateTime(),
                ]),
                Grid::make(3)->schema([
                    TextEntry::make('email_verified_at')
                        ->label('Email Verified')
                        ->dateTime()
                        ->placeholder('Not verified'),
                    TextEntry::make('onboarding_completed_at')
                        ->label('Onboarding Completed')
                        ->dateTime()
                        ->placeholder('Incomplete'),
                    TextEntry::make('device_tokens_count')
                        ->label('Device Tokens')
                        ->state(fn (User $u) => $u->deviceTokens()->count()),
                ]),
            ]),

            Section::make('Profile')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('profile.username'),
                    TextEntry::make('profile.display_name')->placeholder('—'),
                    TextEntry::make('profile.place_name')->label('Location')->placeholder('—'),
                ]),
                Grid::make(1)->schema([
                    TextEntry::make('profile.bio')->placeholder('—'),
                ]),
                Grid::make(4)->schema([
                    TextEntry::make('profile.followers_count')->label('Followers'),
                    TextEntry::make('profile.following_count')->label('Following'),
                    TextEntry::make('profile.rotations_count')->label('Rotations'),
                    TextEntry::make('profile.takes_count')->label('Takes'),
                ]),
            ]),

            Section::make('Activity')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('takes_written')
                        ->label('Takes Written')
                        ->state(fn (User $u) => $u->takes()->count()),
                    TextEntry::make('rotations_created')
                        ->label('Rotations Created')
                        ->state(fn (User $u) => $u->rotations()->count()),
                    TextEntry::make('reports_filed')
                        ->label('Reports Filed')
                        ->state(fn (User $u) => $u->reports()->count()),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('profile.username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profile.display_name')
                    ->label('Display Name')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('email')->searchable(),
                IconColumn::make('email_verified')
                    ->label('Verified')
                    ->boolean()
                    ->state(fn (User $u) => $u->email_verified_at !== null),
                IconColumn::make('onboarded')
                    ->label('Onboarded')
                    ->boolean()
                    ->state(fn (User $u) => $u->onboarding_completed_at !== null),
                TextColumn::make('profile.followers_count')
                    ->label('Followers')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('onboarding_completed')
                    ->label('Onboarding')
                    ->placeholder('All users')
                    ->trueLabel('Completed')
                    ->falseLabel('Incomplete')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('onboarding_completed_at'),
                        false: fn (Builder $query) => $query->whereNull('onboarding_completed_at'),
                    ),
                TernaryFilter::make('email_verified')
                    ->label('Email Verified')
                    ->placeholder('All users')
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    ),
                Filter::make('has_content')
                    ->label('Has Content')
                    ->query(fn (Builder $query) => $query->whereHas('takes')->orWhereHas('rotations')),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalDescription('This generates a short-lived API token for this user. Copy and use it in your API client. It expires in 1 hour.')
                    ->visible(fn () => auth()->user()->can('users.impersonate'))
                    ->action(function (User $user) {
                        $token = $user->createToken(
                            'admin-impersonation',
                            ['*'],
                            now()->addHour()
                        )->plainTextToken;

                        activity()->causedBy(auth()->user())->performedOn($user)
                            ->log("Generated impersonation token for user: {$user->email}");

                        Notification::make()
                            ->title('Impersonation token generated')
                            ->body($token)
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
                Action::make('export_data')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('Download a full JSON export of this user\'s profile, rotations, and takes.')
                    ->visible(fn () => auth()->user()->can('compliance.manage'))
                    ->action(function (User $user) {
                        $data = app(ExportService::class)->build($user, request());
                        $filename = 'user-export-' . $user->id . '-' . now()->format('Y-m-d') . '.json';

                        activity()->causedBy(auth()->user())->performedOn($user)
                            ->log("Exported data for user: {$user->email}");

                        return response()->streamDownload(
                            fn () => print json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                            $filename,
                            ['Content-Type' => 'application/json']
                        );
                    }),
                Action::make('revoke_tokens')
                    ->label('Revoke Sessions')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()->can('users.edit'))
                    ->action(function (User $user) {
                        $user->tokens()->delete();
                        $user->deviceTokens()->delete();
                        activity()
                            ->causedBy(auth()->user())
                            ->performedOn($user)
                            ->log('Revoked all sessions and device tokens');
                        Notification::make()
                            ->title('Sessions and device tokens revoked')
                            ->success()
                            ->send();
                    }),
                Action::make('delete_account')
                    ->label('Delete Account')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This will permanently delete the user account and all associated data. This cannot be undone.')
                    ->visible(fn () => auth()->user()->can('users.delete'))
                    ->action(function (User $user) {
                        activity()
                            ->causedBy(auth()->user())
                            ->performedOn($user)
                            ->withProperties(['email' => $user->email])
                            ->log('Deleted user account');
                        $user->tokens()->delete();
                        $user->deviceTokens()->delete();
                        $user->profile()->delete();
                        $user->delete();
                        Notification::make()
                            ->title('User account deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('profile'));
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\UserResource\RelationManagers\TakesRelationManager::class,
            \App\Filament\Resources\UserResource\RelationManagers\RotationsRelationManager::class,
            \App\Filament\Resources\UserResource\RelationManagers\ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view'  => Pages\ViewUser::route('/{record}'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
