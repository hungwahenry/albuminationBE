<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RotationCommentResource\Pages;
use App\Models\RotationComment;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RotationCommentResource extends Resource
{
    protected static ?string $model = RotationComment::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')->label('Author')->searchable(),
                TextColumn::make('rotation.title')->label('Rotation')->searchable()->limit(35),
                TextColumn::make('body')->limit(60)->placeholder('GIF only'),
                TextColumn::make('gif_url')->label('GIF')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Yes' : '—')
                    ->placeholder('—'),
                IconColumn::make('is_deleted')->label('Deleted')->boolean()
                    ->trueColor('danger')->falseColor('success'),
                TextColumn::make('parent_id')->label('Reply to')
                    ->formatStateUsing(fn (?int $state) => $state ? "#{$state}" : 'Top-level')
                    ->placeholder('Top-level'),
                TextColumn::make('created_at')->label('Posted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_deleted')
                    ->label('Deleted')
                    ->placeholder('All')
                    ->trueLabel('Deleted only')
                    ->falseLabel('Not deleted'),
                Filter::make('top_level')
                    ->label('Top-level only')
                    ->query(fn (Builder $query) => $query->whereNull('parent_id')),
            ])
            ->actions([
                Action::make('soft_delete')
                    ->label('Mark Deleted')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (RotationComment $r) => !$r->is_deleted && auth()->user()->can('content.delete'))
                    ->action(function (RotationComment $record) {
                        $record->update(['is_deleted' => true]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Soft-deleted rotation comment ID {$record->id}");
                        Notification::make()->title('Comment marked as deleted')->success()->send();
                    }),
                Action::make('hard_delete')
                    ->label('Hard Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This permanently removes the comment and any replies to it.')
                    ->visible(fn () => auth()->user()->can('content.delete'))
                    ->action(function (RotationComment $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Hard-deleted rotation comment ID {$record->id}");
                        $record->replies()->delete();
                        $record->delete();
                        Notification::make()->title('Comment permanently deleted')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_soft_delete')
                        ->label('Mark Deleted')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('content.delete'))
                        ->action(function (Collection $records) {
                            $records->each->update(['is_deleted' => true]);
                            Notification::make()->title("{$records->count()} comments marked as deleted")->success()->send();
                        }),
                    BulkAction::make('bulk_hard_delete')
                        ->label('Hard Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('content.delete'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each(fn (RotationComment $r) => $r->replies()->delete() && $r->delete());
                            Notification::make()->title("{$count} comments permanently deleted")->success()->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile', 'rotation']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRotationComments::route('/'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()->can('rotations.view'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('rotations.view'); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('content.delete'); }
}
