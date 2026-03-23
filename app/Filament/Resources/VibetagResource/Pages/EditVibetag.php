<?php

namespace App\Filament\Resources\VibetagResource\Pages;

use App\Filament\Resources\VibetagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVibetag extends EditRecord
{
    protected static string $resource = VibetagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    activity()->causedBy(auth()->user())->performedOn($this->record)
                        ->log("Deleted vibetag: {$this->record->name}");
                }),
        ];
    }

    protected function afterSave(): void
    {
        activity()->causedBy(auth()->user())->performedOn($this->record)
            ->log("Renamed vibetag to: {$this->record->name}");
    }
}
