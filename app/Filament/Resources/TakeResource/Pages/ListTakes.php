<?php

namespace App\Filament\Resources\TakeResource\Pages;

use App\Filament\Resources\TakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTakes extends ListRecords
{
    protected static string $resource = TakeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // read-only
        ];
    }
}
