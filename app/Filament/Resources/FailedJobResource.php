<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FailedJobResource\Pages;
use App\Models\FailedJob;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Failed Jobs';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = FailedJob::count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Job Details')->schema([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('uuid')->label('UUID')->copyable(),
                TextEntry::make('connection'),
                TextEntry::make('queue'),
                TextEntry::make('failed_at')->dateTime(),
            ])->columns(3),

            Section::make('Exception')->schema([
                TextEntry::make('exception')
                    ->label('Stack Trace')
                    ->columnSpanFull()
                    ->html(false),
            ]),

            Section::make('Payload')->schema([
                TextEntry::make('payload_display')
                    ->label('Payload (JSON)')
                    ->state(function (FailedJob $record): string {
                        $decoded = json_decode($record->payload, true);
                        return $decoded
                            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : $record->payload;
                    })
                    ->columnSpanFull()
                    ->html(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('queue')->badge()->searchable(),
                TextColumn::make('uuid')->label('UUID')->limit(20)->copyable(),
                TextColumn::make('job_class')
                    ->label('Job')
                    ->state(function (FailedJob $record): string {
                        $payload = json_decode($record->payload, true);
                        return class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown');
                    }),
                TextColumn::make('exception_summary')
                    ->label('Error')
                    ->state(fn (FailedJob $record) => str($record->exception)->before("\n")->limit(80)->toString())
                    ->placeholder('—'),
                TextColumn::make('failed_at')->label('Failed At')->dateTime()->sortable(),
            ])
            ->defaultSort('failed_at', 'desc')
            ->filters([
                SelectFilter::make('queue')
                    ->options(fn () => FailedJob::distinct()->pluck('queue', 'queue')->toArray()),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()->can('system.queue'))
                    ->action(function (FailedJob $record) {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Retried failed job: {$record->uuid}");
                        Notification::make()->title('Job queued for retry')->success()->send();
                    }),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('system.queue'))
                    ->before(fn (FailedJob $record) => activity()->causedBy(auth()->user())
                        ->log("Deleted failed job: {$record->uuid}")),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('retry_selected')
                        ->label('Retry Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('system.queue'))
                        ->action(function (Collection $records) {
                            $uuids = $records->pluck('uuid')->toArray();
                            Artisan::call('queue:retry', ['id' => $uuids]);
                            activity()->causedBy(auth()->user())
                                ->log('Retried ' . count($uuids) . ' failed jobs');
                            Notification::make()->title(count($uuids) . ' jobs queued for retry')->success()->send();
                        }),
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('system.queue')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFailedJobs::route('/'),
            'view'  => Pages\ViewFailedJob::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
