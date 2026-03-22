<?php

namespace App\Filament\Resources\DeviceTokenResource\Pages;

use App\Filament\Resources\DeviceTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeviceToken extends EditRecord
{
    protected static string $resource = DeviceTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
