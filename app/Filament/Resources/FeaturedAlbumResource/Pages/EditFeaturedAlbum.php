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
        return [
            DeleteAction::make()
                ->before(function () {
                    activity()->causedBy(auth()->user())->performedOn($this->record)
                        ->log("Removed featured album: {$this->record->album->title} (position {$this->record->sort_order})");
                }),
        ];
    }

    protected function afterSave(): void
    {
        $album = $this->record->album;
        activity()->causedBy(auth()->user())->performedOn($this->record)
            ->log("Updated featured album: {$album->title} to position {$this->record->sort_order}");
    }
}
