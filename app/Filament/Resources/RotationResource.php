<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RotationResource\Pages;
use App\Filament\Resources\RotationResource\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\RotationResource\RelationManagers\ItemsRelationManager;
use App\Models\Rotation;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RotationResource extends Resource
{
    protected static ?string $model = Rotation::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Rotation')->schema([
                Grid::make(4)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('status')->badge()->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'draft'     => 'gray',
                        default     => 'gray',
                    }),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('published_at')->label('Published')->dateTime()->placeholder('Not published'),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('user.email')->label('Author'),
                    TextEntry::make('title'),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('caption')->placeholder('—')->columnSpanFull(),
                ]),
                Grid::make(4)->schema([
                    TextEntry::make('is_public')->label('Public')
                        ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                        ->badge()->color(fn (bool $state) => $state ? 'success' : 'gray'),
                    TextEntry::make('is_ranked')->label('Ranked')
                        ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                        ->badge()->color(fn (bool $state) => $state ? 'info' : 'gray'),
                    TextEntry::make('items_count')->label('Items'),
                    TextEntry::make('loves_count')->label('Loves'),
                ]),
            ]),
            Section::make('Vibetags')->schema([
                TextEntry::make('vibetags_list')
                    ->label('Vibetags')
                    ->state(fn (Rotation $r) => $r->vibetags->pluck('name')->implode(', '))
                    ->placeholder('None'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->size(48),
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')->label('Author')->searchable(),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'published' => 'success',
                    'draft'     => 'gray',
                    default     => 'gray',
                }),
                IconColumn::make('is_public')->label('Public')->boolean(),
                IconColumn::make('is_ranked')->label('Ranked')->boolean(),
                TextColumn::make('items_count')->label('Items')->sortable(),
                TextColumn::make('loves_count')->label('Loves')->sortable(),
                TextColumn::make('comments_count')->label('Comments')->sortable(),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['published' => 'Published', 'draft' => 'Draft']),
                SelectFilter::make('type')
                    ->options(['album' => 'Album', 'track' => 'Track']),
                TernaryFilter::make('is_public')
                    ->label('Visibility')
                    ->trueLabel('Public')
                    ->falseLabel('Private'),
                TernaryFilter::make('is_ranked')
                    ->label('Ranked'),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Rotation $r) => $r->status === 'published' && auth('admin')->user()?->can('content.delete'))
                    ->action(function (Rotation $record) {
                        $record->update(['status' => 'draft', 'published_at' => null]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Unpublished rotation ID {$record->id}: {$record->title}");
                        Notification::make()->title('Rotation unpublished')->success()->send();
                    }),
                Action::make('hard_delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This permanently removes the rotation, all its items, comments, and loves.')
                    ->visible(fn () => auth('admin')->user()?->can('content.delete'))
                    ->action(function (Rotation $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Hard-deleted rotation ID {$record->id}: {$record->title}");
                        $record->comments()->delete();
                        $record->items()->delete();
                        $record->delete();
                        Notification::make()->title('Rotation permanently deleted')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth('admin')->user()?->can('content.delete'))
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'draft', 'published_at' => null]);
                            activity()->causedBy(auth()->user())
                                ->log("Bulk unpublished {$records->count()} rotations");
                            Notification::make()->title("{$records->count()} rotations unpublished")->success()->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile', 'vibetags']));
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRotations::route('/'),
            'view'  => Pages\ViewRotation::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool { return auth('admin')->user()?->can('rotations.view'); }
    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool { return auth('admin')->user()?->can('rotations.view'); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return auth('admin')->user()?->can('content.delete'); }
}
