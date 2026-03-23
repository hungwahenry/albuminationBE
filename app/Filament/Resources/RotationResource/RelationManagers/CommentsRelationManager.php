<?php

namespace App\Filament\Resources\RotationResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Models\RotationComment;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')->label('Author')
                    ->url(fn (RotationComment $r) => $r->user_id
                        ? UserResource::getUrl('view', ['record' => $r->user_id])
                        : null),
                TextColumn::make('body')->limit(60)->placeholder('GIF only'),
                TextColumn::make('parent_id')->label('Reply to')
                    ->formatStateUsing(fn (?int $state) => $state ? "#{$state}" : 'Top-level')
                    ->placeholder('Top-level'),
                IconColumn::make('is_deleted')->label('Deleted')->boolean()
                    ->trueColor('danger')->falseColor('success'),
                TextColumn::make('created_at')->label('Posted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_deleted')->label('Deleted')
                    ->placeholder('All')->trueLabel('Deleted only')->falseLabel('Active only'),
            ])
            ->actions([
                Action::make('soft_delete')
                    ->label('Mark Deleted')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (RotationComment $r) => !$r->is_deleted && auth('admin')->user()?->can('content.delete'))
                    ->action(function (RotationComment $record) {
                        $record->update(['is_deleted' => true]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Soft-deleted rotation comment ID {$record->id}");
                        Notification::make()->title('Comment marked as deleted')->success()->send();
                    }),
                Action::make('hard_delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => auth('admin')->user()?->can('content.delete'))
                    ->action(function (RotationComment $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Hard-deleted rotation comment ID {$record->id}");
                        $record->replies()->delete();
                        $record->delete();
                        Notification::make()->title('Comment deleted')->success()->send();
                    }),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn ($query) => $query->with('user.profile'));
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
