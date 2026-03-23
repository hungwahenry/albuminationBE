<?php

namespace App\Filament\Resources\VibetagResource\Pages;

use App\Filament\Resources\VibetagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVibetag extends CreateRecord
{
    protected static string $resource = VibetagResource::class;

    protected function afterCreate(): void
    {
        activity()->causedBy(auth()->user())->performedOn($this->record)
            ->log("Created vibetag: {$this->record->name}");
    }
}
