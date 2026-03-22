<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportsAgainstRelationManager extends RelationManager
{
    protected static string $relationship = 'reportsAgainst';

    protected static ?string $title = 'Reports Against This User';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.profile.username')->label('Reported By')
                    ->url(fn (Report $r) => $r->user_id
                        ? \App\Filament\Resources\UserResource::getUrl('view', ['record' => $r->user_id])
                        : null)
                    ->placeholder('—'),
                TextColumn::make('reason.label')->label('Reason')->placeholder('—'),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'resolved' => 'success',
                    'rejected' => 'danger',
                    'pending'  => 'warning',
                    default    => 'gray',
                }),
                TextColumn::make('body')->limit(60)->placeholder('—'),
                TextColumn::make('created_at')->label('Filed')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'resolved' => 'Resolved', 'rejected' => 'Rejected']),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Report $record) => ReportResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn ($query) => $query->with(['user.profile', 'reason']));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
