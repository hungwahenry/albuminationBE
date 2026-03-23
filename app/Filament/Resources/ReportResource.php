<?php

namespace App\Filament\Resources;

use App\Events\ReportResolved;
use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\RotationResource;
use App\Filament\Resources\TakeResource;
use App\Filament\Resources\UserResource;
use App\Models\Report;
use App\Models\ReportReason;
use App\Models\Rotation;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) Report::where('status', 'pending')->count() ?: null;
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
            Section::make('Report Details')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('Report ID'),
                    TextEntry::make('status')->badge()->color(fn (string $state) => match ($state) {
                        'pending'    => 'warning',
                        'reviewed'   => 'info',
                        'dismissed'  => 'gray',
                        'actioned'   => 'success',
                        default      => 'gray',
                    }),
                    TextEntry::make('created_at')->label('Submitted')->dateTime(),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('reason.label')->label('Reason'),
                    TextEntry::make('reportable_type')
                        ->label('Content Type')
                        ->formatStateUsing(fn (string $state) => class_basename($state)),
                ]),
                TextEntry::make('body')->label('Additional Context')->placeholder('None provided'),
            ]),

            Section::make('Reporter')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('user.id')->label('User ID'),
                    TextEntry::make('user.email')->label('Email')
                        ->url(fn (Report $r) => $r->user_id
                            ? UserResource::getUrl('view', ['record' => $r->user_id])
                            : null),
                    TextEntry::make('user.profile.username')->label('Username')
                        ->url(fn (Report $r) => $r->user_id
                            ? UserResource::getUrl('view', ['record' => $r->user_id])
                            : null),
                ]),
            ]),

            Section::make('Reported Content')->schema([
                TextEntry::make('reportable_summary')
                    ->label('Content')
                    ->state(function (Report $record): string {
                        $r = $record->reportable;
                        if (!$r) return 'Content no longer exists';
                        return match (true) {
                            $r instanceof \App\Models\Take            => "Take: {$r->body}",
                            $r instanceof \App\Models\TakeReply       => "Reply: {$r->body}",
                            $r instanceof \App\Models\Rotation        => "Rotation: {$r->title}",
                            $r instanceof \App\Models\RotationComment => "Comment: {$r->body}",
                            $r instanceof \App\Models\User            => "User: {$r->email}",
                            default                                   => 'Unknown content type',
                        };
                    })
                    ->url(function (Report $record): ?string {
                        $r = $record->reportable;
                        if (!$r) return null;
                        return match (true) {
                            $r instanceof Take     => TakeResource::getUrl('view', ['record' => $r->id]),
                            $r instanceof Rotation => RotationResource::getUrl('view', ['record' => $r->id]),
                            $r instanceof \App\Models\User => UserResource::getUrl('view', ['record' => $r->id]),
                            default => null,
                        };
                    })
                    ->openUrlInNewTab(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')
                    ->label('Reporter')
                    ->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state) => class_basename($state)),
                TextColumn::make('reason.label')->label('Reason'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'   => 'warning',
                        'reviewed'  => 'info',
                        'dismissed' => 'gray',
                        'actioned'  => 'success',
                        default     => 'gray',
                    }),
                TextColumn::make('created_at')->label('Submitted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'reviewed'  => 'Reviewed',
                        'dismissed' => 'Dismissed',
                        'actioned'  => 'Actioned',
                    ]),
                SelectFilter::make('reportable_type')
                    ->label('Content Type')
                    ->options([
                        'App\\Models\\Take'            => 'Take',
                        'App\\Models\\TakeReply'       => 'Reply',
                        'App\\Models\\Rotation'        => 'Rotation',
                        'App\\Models\\RotationComment' => 'Comment',
                        'App\\Models\\User'            => 'User',
                    ]),
                SelectFilter::make('report_reason_id')
                    ->label('Reason')
                    ->options(ReportReason::pluck('label', 'id')),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('mark_reviewed')
                    ->label('Mark Reviewed')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Report $r) => $r->status === 'pending' && auth()->user()->can('reports.action'))
                    ->action(function (Report $report) {
                        $report->update(['status' => 'reviewed']);
                        activity()->causedBy(auth()->user())->performedOn($report)->log('Marked report as reviewed');
                        Notification::make()->title('Report marked as reviewed')->success()->send();
                    }),
                Action::make('action_report')
                    ->label('Action')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('This will notify the reporter that their report has been actioned.')
                    ->visible(fn (Report $r) => in_array($r->status, ['pending', 'reviewed']) && auth()->user()->can('reports.action'))
                    ->action(function (Report $report) {
                        $report->update(['status' => 'actioned']);
                        ReportResolved::dispatch($report, 'resolved');
                        activity()->causedBy(auth()->user())->performedOn($report)->log('Actioned report — reporter notified');
                        Notification::make()->title('Report actioned and reporter notified')->success()->send();
                    }),
                Action::make('dismiss')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Report $r) => in_array($r->status, ['pending', 'reviewed']) && auth()->user()->can('reports.action'))
                    ->action(function (Report $report) {
                        $report->update(['status' => 'dismissed']);
                        ReportResolved::dispatch($report, 'dismissed');
                        activity()->causedBy(auth()->user())->performedOn($report)->log('Dismissed report — reporter notified');
                        Notification::make()->title('Report dismissed')->success()->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_reviewed')
                        ->label('Mark Reviewed')
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('reports.action'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['status' => 'reviewed']);
                            activity()->causedBy(auth()->user())->log("Bulk marked {$count} reports as reviewed");
                        }),
                    BulkAction::make('bulk_dismiss')
                        ->label('Dismiss Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->can('reports.action'))
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['status' => 'dismissed']);
                            activity()->causedBy(auth()->user())->log("Bulk dismissed {$count} reports");
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user.profile', 'reason']));
    }

    public static function getRelationManagers(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'view'  => Pages\ViewReport::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
