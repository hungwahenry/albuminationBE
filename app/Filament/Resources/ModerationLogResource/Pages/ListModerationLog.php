<?php

namespace App\Filament\Resources\ModerationLogResource\Pages;

use App\Filament\Resources\ModerationLogResource;
use Filament\Resources\Pages\ListRecords;

class ListModerationLog extends ListRecords
{
    protected static string $resource = ModerationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
