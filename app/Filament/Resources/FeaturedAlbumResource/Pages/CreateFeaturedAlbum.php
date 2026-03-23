<?php

namespace App\Filament\Resources\FeaturedAlbumResource\Pages;

use App\Filament\Resources\FeaturedAlbumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeaturedAlbum extends CreateRecord
{
    protected static string $resource = FeaturedAlbumResource::class;

    protected function afterCreate(): void
    {
        $album = $this->record->album;
        activity()->causedBy(auth()->user())->performedOn($this->record)
            ->log("Added featured album: {$album->title} at position {$this->record->sort_order}");
    }
}
