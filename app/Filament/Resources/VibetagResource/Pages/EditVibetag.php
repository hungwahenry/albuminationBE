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
        return [DeleteAction::make()];
    }
}
