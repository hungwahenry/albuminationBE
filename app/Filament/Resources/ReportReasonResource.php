<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportReasonResource\Pages;
use App\Models\ReportReason;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportReasonResource extends Resource
{
    protected static ?string $model = ReportReason::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Reason')->schema([
                TextInput::make('label')->required(),
                TextInput::make('description')->nullable(),
                Select::make('reportable_type')
                    ->label('Applies To')
                    ->options([
                        ''                             => 'All content types',
                        'App\\Models\\Take'            => 'Take',
                        'App\\Models\\TakeReply'       => 'Reply',
                        'App\\Models\\Rotation'        => 'Rotation',
                        'App\\Models\\RotationComment' => 'Comment',
                        'App\\Models\\User'            => 'User',
                    ])
                    ->placeholder('All content types')
                    ->nullable(),
            ])->columns(1),

            Section::make('Display')->schema([
                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->integer()
                    ->required()
                    ->default(fn () => (ReportReason::max('sort_order') ?? 0) + 1),
                Toggle::make('is_active')->label('Active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('label')->searchable(),
                TextColumn::make('reportable_type')
                    ->label('Applies To')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : 'All')
                    ->placeholder('All'),
                TextColumn::make('description')->limit(60)->placeholder('—'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('reports_count')
                    ->label('Reports')
                    ->counts('reports')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('reportable_type')
                    ->label('Applies To')
                    ->options([
                        'App\\Models\\Take'            => 'Take',
                        'App\\Models\\TakeReply'       => 'Reply',
                        'App\\Models\\Rotation'        => 'Rotation',
                        'App\\Models\\RotationComment' => 'Comment',
                        'App\\Models\\User'            => 'User',
                    ]),
            ])
            ->actions([
                Action::make('toggle_active')
                    ->label(fn (ReportReason $r) => $r->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (ReportReason $r) => $r->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (ReportReason $r) => $r->is_active ? 'warning' : 'success')
                    ->visible(fn () => auth('admin')->user()?->can('report_reasons.manage'))
                    ->action(function (ReportReason $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log($record->is_active ? 'Activated report reason' : 'Deactivated report reason');
                        Notification::make()
                            ->title('Report reason ' . ($record->is_active ? 'activated' : 'deactivated'))
                            ->success()->send();
                    }),
                EditAction::make()
                    ->visible(fn () => auth('admin')->user()?->can('report_reasons.manage')),
                DeleteAction::make()
                    ->visible(fn (ReportReason $r) => auth('admin')->user()?->can('report_reasons.manage') && $r->reports()->count() === 0)
                    ->before(function (ReportReason $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Deleted report reason: {$record->label}");
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReportReasons::route('/'),
            'create' => Pages\CreateReportReason::route('/create'),
            'edit'   => Pages\EditReportReason::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth('admin')->user()?->can('report_reasons.manage');
    }
}
