<?php

namespace App\Filament\Resources\TakeResource\Pages;

use App\Filament\Resources\TakeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTake extends EditRecord
{
    protected static string $resource = TakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
