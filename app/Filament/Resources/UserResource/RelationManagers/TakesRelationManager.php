<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\TakeResource;
use App\Models\Take;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TakesRelationManager extends RelationManager
{
    protected static string $relationship = 'takes';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('album.title')->label('Album')->limit(30)->searchable(),
                TextColumn::make('rating')->badge()->color(fn (string $state) => match ($state) {
                    'hit'  => 'success',
                    'miss' => 'danger',
                    default => 'gray',
                }),
                TextColumn::make('body')->limit(60)->placeholder('—'),
                IconColumn::make('is_deleted')->label('Deleted')->boolean()
                    ->trueColor('danger')->falseColor('success'),
                TextColumn::make('created_at')->label('Posted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_deleted')->label('Deleted')
                    ->placeholder('All')->trueLabel('Deleted only')->falseLabel('Active only'),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Take $record) => TakeResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([])
            ->modifyQueryUsing(fn ($query) => $query->with('album'));
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
