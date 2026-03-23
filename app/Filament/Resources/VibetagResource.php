<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VibetagResource\Pages;
use App\Models\Vibetag;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VibetagResource extends Resource
{
    protected static ?string $model = Vibetag::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('usage_count')->label('Used In')->sortable(),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('usage_count', 'desc')
            ->actions([
                EditAction::make()
                    ->label('Rename')
                    ->visible(fn () => auth()->user()->can('vibetags.manage')),
                Action::make('merge')
                    ->label('Merge Into…')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('target_id')
                            ->label('Merge into this tag')
                            ->options(fn (Vibetag $r) => Vibetag::where('id', '!=', $r->id)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->visible(fn () => auth()->user()->can('vibetags.manage'))
                    ->action(function (Vibetag $record, array $data) {
                        $target = Vibetag::findOrFail($data['target_id']);
                        // Re-attach all rotations to target tag, avoiding duplicates
                        $rotationIds = $record->rotations()->pluck('rotation_id');
                        $target->rotations()->syncWithoutDetaching($rotationIds);
                        $target->increment('usage_count', $record->usage_count);
                        $record->rotations()->detach();
                        $record->delete();
                        activity()->causedBy(auth()->user())
                            ->log("Merged vibetag '{$record->name}' into '{$target->name}'");
                        Notification::make()->title("'{$record->name}' merged into '{$target->name}'")->success()->send();
                    }),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('vibetags.manage'))
                    ->before(function (Vibetag $record) {
                        activity()->causedBy(auth()->user())->performedOn($record)
                            ->log("Deleted vibetag: {$record->name}");
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVibetags::route('/'),
            'create' => Pages\CreateVibetag::route('/create'),
            'edit'   => Pages\EditVibetag::route('/{record}/edit'),
        ];
    }
}
