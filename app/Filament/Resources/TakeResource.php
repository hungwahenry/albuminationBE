<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TakeResource\Pages;
use App\Models\Take;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TakeResource extends Resource
{
    protected static ?string $model = Take::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Take')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('rating')->badge()->color(fn (string $state) => match ($state) {
                        'hit'  => 'success',
                        'miss' => 'danger',
                        default => 'gray',
                    }),
                    TextEntry::make('is_deleted')->label('Deleted')
                        ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                        ->badge()->color(fn (bool $state) => $state ? 'danger' : 'success'),
                    TextEntry::make('created_at')->label('Posted')->dateTime(),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('user.email')->label('Author'),
                    TextEntry::make('album.title')->label('Album'),
                ]),
                TextEntry::make('body')->label('Content')->columnSpanFull()->placeholder('—'),
            ]),
            Section::make('Engagement')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('replies_count')->label('Replies')
                        ->state(fn (Take $r) => $r->replies()->count()),
                    TextEntry::make('reactions_count')->label('Reactions')
                        ->state(fn (Take $r) => $r->reactions()->count()),
                    TextEntry::make('edited_at')->label('Edited At')->dateTime()->placeholder('Never'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')->label('Author')->searchable(),
                TextColumn::make('album.title')->label('Album')->searchable()->limit(30),
                TextColumn::make('rating')->badge()->color(fn (string $state) => match ($state) {
                    'hit'  => 'success',
                    'miss' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('body')->limit(60)->placeholder('—'),
                IconColumn::make('is_deleted')->label('Deleted')->boolean()
                    ->trueColor('danger')->falseColor('success'),
                TextColumn::make('replies_count')->counts('replies')->label('Replies')->sortable(),
                TextColumn::make('created_at')->label('Posted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('rating')
                    ->options(['hit' => 'Hit', 'miss' => 'Miss']),
                TernaryFilter::make('is_deleted')
                    ->label('Deleted')
                    ->placeholder('All')
                    ->trueLabel('Deleted only')
                    ->falseLabel('Not deleted'),
                Filter::make('has_replies')
                    ->label('Has Replies')
                    ->query(fn (Builder $query) => $query->whereHas('replies')),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('soft_delete')
                    ->label('Mark Deleted')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Take $r) => !$r->is_deleted && auth()->user()->can('content.delete'))
                    ->action(function (Take $record) {
                        $record->update(['is_deleted' => true]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Soft-deleted take ID {$record->id}");
                        Notification::make()->title('Take marked as deleted')->success()->send();
                    }),
                Action::make('hard_delete')
                    ->label('Hard Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This permanently removes the take and all its replies and reactions. This cannot be undone.')
                    ->visible(fn () => auth()->user()->can('content.delete'))
                    ->action(function (Take $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Hard-deleted take ID {$record->id} by {$record->user?->email}");
                        $record->replies()->delete();
                        $record->reactions()->delete();
                        $record->delete();
                        Notification::make()->title('Take permanently deleted')->success()->send();
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
                            activity()->causedBy(auth()->user())
                                ->log("Bulk soft-deleted {$records->count()} takes");
                            Notification::make()->title("{$records->count()} takes marked as deleted")->success()->send();
                        }),
                    BulkAction::make('bulk_hard_delete')
                        ->label('Hard Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('content.delete'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each(function (Take $r) {
                                $r->replies()->delete();
                                $r->reactions()->delete();
                                $r->delete();
                            });
                            activity()->causedBy(auth()->user())
                                ->log("Hard-deleted {$count} takes");
                            Notification::make()->title("{$count} takes permanently deleted")->success()->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile', 'album']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTakes::route('/'),
            'view'  => Pages\ViewTake::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool { return auth()->user()->can('takes.view'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('takes.view'); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return auth()->user()->can('content.delete'); }
}
