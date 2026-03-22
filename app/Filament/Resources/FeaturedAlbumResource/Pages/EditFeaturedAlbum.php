<?php

namespace App\Filament\Resources\FeaturedAlbumResource\Pages;

use App\Filament\Resources\FeaturedAlbumResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedAlbum extends EditRecord
{
    protected static string $resource = FeaturedAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
