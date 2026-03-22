<?php

namespace App\Filament\Resources\RotationResource\Pages;

use App\Filament\Resources\RotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRotation extends EditRecord
{
    protected static string $resource = RotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
