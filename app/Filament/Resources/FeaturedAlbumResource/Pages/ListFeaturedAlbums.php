<?php

namespace App\Filament\Resources\FeaturedAlbumResource\Pages;

use App\Filament\Resources\FeaturedAlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedAlbums extends ListRecords
{
    protected static string $resource = FeaturedAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
