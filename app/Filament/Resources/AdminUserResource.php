<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages;
use App\Models\AdminUser;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Admin Accounts';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Account')->schema([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state) => Hash::make($state))
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep current password' : null),
            ])->columns(2),

            Section::make('Role & Access')->schema([
                Select::make('roles')
                    ->label('Role')
                    ->options(Role::where('guard_name', 'admin')->pluck('name', 'name')->map(fn ($n) => str($n)->replace('_', ' ')->title()->toString()))
                    ->required()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, AdminUser $record = null) {
                        if ($record?->exists) {
                            $component->state($record->roles->first()?->name);
                        }
                    }),
                Toggle::make('is_active')->label('Active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString()),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('last_login_at')->label('Last Login')->dateTime()->sortable()->placeholder('Never'),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(Role::where('guard_name', 'admin')->pluck('name', 'name')->map(fn ($n) => str($n)->replace('_', ' ')->title()->toString()))
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('roles', fn ($q) => $q->where('name', $data['value']))
                        : $query),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Deactivated'),
            ])
            ->actions([
                Action::make('toggle_active')
                    ->label(fn (AdminUser $r) => $r->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (AdminUser $r) => $r->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (AdminUser $r) => $r->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->visible(fn (AdminUser $r) => auth()->user()->can('admin_users.manage') && $r->id !== auth()->id())
                    ->action(function (AdminUser $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log($record->is_active ? 'Activated admin account' : 'Deactivated admin account');
                        Notification::make()
                            ->title('Admin account ' . ($record->is_active ? 'activated' : 'deactivated'))
                            ->success()->send();
                    }),
                Action::make('reset_password')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->visible(fn () => auth()->user()->can('admin_users.manage'))
                    ->action(function (AdminUser $record, array $data) {
                        $record->update(['password' => Hash::make($data['new_password'])]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Reset password for admin: {$record->email}");
                        Notification::make()->title('Password reset successfully')->success()->send();
                    }),
                EditAction::make()
                    ->visible(fn () => auth()->user()->can('admin_users.manage'))
                    ->using(function (AdminUser $record, array $data) {
                        $record->update(collect($data)->except('roles')->toArray());
                        if (isset($data['roles'])) {
                            $record->syncRoles([$data['roles']]);
                        }
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Updated admin account: {$record->email}");
                        return $record;
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit'   => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('admin_users.manage');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('admin_users.manage');
    }
}
