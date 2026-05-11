<?php

namespace App\Filament\Resources\ModerationCategoryResource\Pages;

use App\Filament\Resources\ModerationCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListModerationCategories extends ListRecords
{
    protected static string $resource = ModerationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
