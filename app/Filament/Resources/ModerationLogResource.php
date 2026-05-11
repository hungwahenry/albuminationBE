<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationLogResource\Pages;
use App\Filament\Resources\UserResource;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ModerationLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?string $navigationLabel = 'Moderation Log';
    protected static ?string $slug = 'moderation-log';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('log_name', 'moderation');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Activity::where('log_name', 'moderation')
            ->where('created_at', '>=', now()->subDay())
            ->count() ?: null;
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
            Section::make('Event')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('id')->label('ID'),
                    TextEntry::make('properties.action')
                        ->label('Action')
                        ->badge()
                        ->placeholder('—'),
                    TextEntry::make('created_at')->label('When')->dateTime(),
                ]),
                TextEntry::make('description')->columnSpanFull(),
            ]),

            Section::make('Initiated by')->schema([
                Grid::make(3)->schema([
                    TextEntry::make('causer_id')->label('User ID')
                        ->url(fn (Activity $r) => $r->causer_id
                            ? UserResource::getUrl('view', ['record' => $r->causer_id])
                            : null),
                    TextEntry::make('causer.email')->label('Email')->placeholder('—'),
                    TextEntry::make('causer.profile.username')->label('Username')->placeholder('—'),
                ]),
            ]),

            Section::make('Target')
                ->visible(fn (Activity $r) => $r->subject_id !== null)
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('subject_id')->label('User ID')
                            ->url(fn (Activity $r) => $r->subject_id && $r->subject_type === \App\Models\User::class
                                ? UserResource::getUrl('view', ['record' => $r->subject_id])
                                : null),
                        TextEntry::make('subject.email')->label('Email')->placeholder('—'),
                        TextEntry::make('subject.profile.username')->label('Username')->placeholder('—'),
                    ]),
                ]),

            Section::make('Flagged content')
                ->visible(fn (Activity $r) => ($r->properties['action'] ?? null) === 'content_blocked')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('properties.field')->label('Field')->badge()->placeholder('—'),
                        TextEntry::make('properties.category')->label('Category')->badge()->color('danger')->placeholder('—'),
                        TextEntry::make('properties.score')->label('Score')
                            ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 3) : '—'),
                    ]),
                    TextEntry::make('properties.excerpt')->label('Excerpt')->columnSpanFull()->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
                TextColumn::make('properties.action')->label('Action')->badge(),
                TextColumn::make('causer.profile.username')->label('Initiated by')->placeholder('—'),
                TextColumn::make('subject.profile.username')->label('Target')->placeholder('—'),
                TextColumn::make('description')->limit(60)->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Action')
                    ->query(function (Builder $q, array $data) {
                        if (!empty($data['value'])) {
                            $q->where('properties->action', $data['value']);
                        }
                    })
                    ->options([
                        'user_blocked'    => 'User blocked',
                        'content_blocked' => 'Content blocked',
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
            'index' => Pages\ListModerationLog::route('/'),
            'view'  => Pages\ViewModerationLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
