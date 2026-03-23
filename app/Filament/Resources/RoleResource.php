<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Roles & Permissions';

    public static function form(Form $form): Form
    {
        $isSuperAdmin = fn (?Role $record) => $record?->name === 'super_admin';

        // Group permissions by their prefix (e.g. "catalog", "users", "system")
        $permissions = Permission::where('guard_name', 'admin')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0]);

        $permissionSections = $permissions->map(fn ($perms, $group) =>
            Section::make(ucwords(str_replace('_', ' ', $group)))
                ->columns(2)
                ->collapsed()
                ->schema([
                    CheckboxList::make('permissions')
                        ->relationship('permissions', 'name', fn ($query) => $query
                            ->where('guard_name', 'admin')
                            ->where('name', 'like', $group . '.%')
                            ->orderBy('name'))
                        ->label(false)
                        ->columns(2)
                        ->disabled($isSuperAdmin),
                ])
        )->values()->all();

        return $form->schema([
            Section::make('Role')->schema([
                TextInput::make('name')
                    ->label('Role Name')
                    ->disabled()
                    ->dehydrated(false),

                Placeholder::make('super_admin_notice')
                    ->label('')
                    ->content('The super_admin role always has all permissions and cannot be modified.')
                    ->visible($isSuperAdmin),
            ]),

            Section::make('Permissions')
                ->description('Select which permissions this role grants. Changes take effect immediately.')
                ->schema($permissionSections),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable(),
                TextColumn::make('guard_name')->label('Guard'),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->sortable(),
                TextColumn::make('updated_at')->label('Last Updated')->date()->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->label(fn (Role $record) => $record->name === 'super_admin' ? 'View' : 'Edit Permissions'),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'edit'  => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canViewAny(): bool { return auth('admin')->user()?->can('admin_users.manage'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return auth('admin')->user()?->can('admin_users.manage'); }
}
