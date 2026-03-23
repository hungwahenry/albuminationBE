<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;

class TokensRelationManager extends RelationManager
{
    protected static string $relationship = 'tokens';

    protected static ?string $title = 'Active Sessions (Sanctum Tokens)';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Token Name'),
                TextColumn::make('abilities')
                    ->formatStateUsing(fn (string|array $state) => implode(', ', is_array($state) ? $state : json_decode($state, true) ?? []))
                    ->placeholder('—'),
                IconColumn::make('expired')
                    ->label('Expired')
                    ->boolean()
                    ->state(fn (PersonalAccessToken $t) => $t->expires_at !== null && $t->expires_at->isPast())
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('last_used_at')->label('Last Used')->since()->placeholder('Never'),
                TextColumn::make('expires_at')->label('Expires')->dateTime()->placeholder('Never'),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => auth('admin')->user()?->can('users.edit'))
                    ->action(function (PersonalAccessToken $record) {
                        activity()
                            ->causedBy(auth()->user())
                            ->performedOn($this->getOwnerRecord())
                            ->withProperties(['token_name' => $record->name])
                            ->log("Revoked Sanctum token '{$record->name}' for user: {$this->getOwnerRecord()->email}");

                        $record->delete();

                        Notification::make()->title('Token revoked')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
