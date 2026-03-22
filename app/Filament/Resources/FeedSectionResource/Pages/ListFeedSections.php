<?php

namespace App\Filament\Resources\FeedSectionResource\Pages;

use App\Filament\Resources\FeedSectionResource;
use Filament\Resources\Pages\ListRecords;

class ListFeedSections extends ListRecords
{
    protected static string $resource = FeedSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
