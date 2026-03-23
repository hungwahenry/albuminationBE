<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TakeReplyResource\Pages;
use App\Models\TakeReply;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TakeReplyResource extends Resource
{
    protected static ?string $model = TakeReply::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 4;

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
                TextColumn::make('take.album.title')->label('Album')->limit(30),
                TextColumn::make('body')->limit(60)->placeholder('GIF only'),
                TextColumn::make('gif_url')->label('GIF')
                    ->formatStateUsing(fn (?string $state) => $state ? 'Yes' : '—')
                    ->placeholder('—'),
                IconColumn::make('is_deleted')->label('Deleted')->boolean()
                    ->trueColor('danger')->falseColor('success'),
                TextColumn::make('created_at')->label('Posted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_deleted')
                    ->label('Deleted')
                    ->placeholder('All')
                    ->trueLabel('Deleted only')
                    ->falseLabel('Not deleted'),
            ])
            ->actions([
                Action::make('soft_delete')
                    ->label('Mark Deleted')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (TakeReply $r) => !$r->is_deleted && auth()->user()->can('content.delete'))
                    ->action(function (TakeReply $record) {
                        $record->update(['is_deleted' => true]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Soft-deleted take reply ID {$record->id}");
                        Notification::make()->title('Reply marked as deleted')->success()->send();
                    }),
                Action::make('hard_delete')
                    ->label('Hard Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This permanently removes the reply.')
                    ->visible(fn () => auth()->user()->can('content.delete'))
                    ->action(function (TakeReply $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Hard-deleted take reply ID {$record->id}");
                        $record->delete();
                        Notification::make()->title('Reply permanently deleted')->success()->send();
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
                            $count = $records->count();
                            $records->each->update(['is_deleted' => true]);
                            activity()->causedBy(auth()->user())->log("Bulk soft-deleted {$count} take replies");
                            Notification::make()->title("{$count} replies marked as deleted")->success()->send();
                        }),
                    BulkAction::make('bulk_hard_delete')
                        ->label('Hard Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('content.delete'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            activity()->causedBy(auth()->user())->log("Bulk hard-deleted {$count} take replies");
                            $records->each->delete();
                            Notification::make()->title("{$count} replies permanently deleted")->success()->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile', 'take.album']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTakeReplies::route('/'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()->can('takes.view'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('takes.view'); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('content.delete'); }
}
