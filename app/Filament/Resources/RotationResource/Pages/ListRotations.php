<?php

namespace App\Filament\Resources\RotationResource\Pages;

use App\Filament\Resources\RotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRotations extends ListRecords
{
    protected static string $resource = RotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // read-only
        ];
    }
}
