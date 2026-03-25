<?php

namespace App\Filament\Resources\BadgeRarityResource\Pages;

use App\Filament\Resources\BadgeRarityResource;
use Filament\Resources\Pages\ListRecords;

class ListBadgeRarities extends ListRecords
{
    protected static string $resource = BadgeRarityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
