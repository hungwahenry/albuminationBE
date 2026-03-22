<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class AuditLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit Log';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Event')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('log_name')->label('Log'),
                    TextEntry::make('created_at')->label('When')->dateTime(),
                ]),
                Grid::make(2)->schema([
                    TextEntry::make('event')->placeholder('—'),
                    TextEntry::make('description'),
                ]),
            ]),

            InfoSection::make('Causer')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('causer_type')
                        ->label('Type')
                        ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                        ->placeholder('—'),
                    TextEntry::make('causer_id')->label('ID')->placeholder('—'),
                    TextEntry::make('causer.email')->label('Email')->placeholder('—'),
                ]),
            ]),

            InfoSection::make('Subject')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('subject_type')
                        ->label('Type')
                        ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                        ->placeholder('—'),
                    TextEntry::make('subject_id')->label('ID')->placeholder('—'),
                ]),
            ]),

            InfoSection::make('Properties')->schema([
                TextEntry::make('properties')
                    ->label('Properties (JSON)')
                    ->state(fn (Activity $record) => $record->properties?->isNotEmpty()
                        ? json_encode($record->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : '—'
                    )
                    ->html(false)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('log_name')->label('Log')->badge()->sortable(),
                TextColumn::make('description')->limit(60)->searchable(),
                TextColumn::make('causer_type')
                    ->label('Causer Type')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : 'System')
                    ->placeholder('System'),
                TextColumn::make('causer_id')->label('Causer ID')->placeholder('—'),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—')
                    ->placeholder('—'),
                TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Log')
                    ->options(fn () => Activity::distinct()->pluck('log_name', 'log_name')->filter()->toArray()),
                SelectFilter::make('causer_type')
                    ->label('Causer Type')
                    ->options([
                        'App\\Models\\AdminUser' => 'Admin',
                        'App\\Models\\User'      => 'User',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view'  => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
