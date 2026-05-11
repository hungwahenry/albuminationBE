<?php

namespace App\Filament\Resources\ModerationCategoryResource\Pages;

use App\Filament\Resources\ModerationCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditModerationCategory extends EditRecord
{
    protected static string $resource = ModerationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
