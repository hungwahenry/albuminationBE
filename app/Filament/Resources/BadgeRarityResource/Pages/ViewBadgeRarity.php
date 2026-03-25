<?php

namespace App\Filament\Resources\BadgeRarityResource\Pages;

use App\Filament\Resources\BadgeRarityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBadgeRarity extends ViewRecord
{
    protected static string $resource = BadgeRarityResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
