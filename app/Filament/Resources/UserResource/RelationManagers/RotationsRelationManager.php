<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\RotationResource;
use App\Models\Rotation;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RotationsRelationManager extends RelationManager
{
    protected static string $relationship = 'rotations';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->limit(40)->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'published' => 'success',
                    'draft'     => 'gray',
                    default     => 'gray',
                }),
                IconColumn::make('is_public')->label('Public')->boolean(),
                TextColumn::make('items_count')->label('Items')->sortable(),
                TextColumn::make('loves_count')->label('Loves')->sortable(),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(['published' => 'Published', 'draft' => 'Draft']),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Rotation $record) => RotationResource::getUrl('view', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
