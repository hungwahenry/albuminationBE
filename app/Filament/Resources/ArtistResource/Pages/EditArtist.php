<?php

namespace App\Filament\Resources\ArtistResource\Pages;

use App\Filament\Resources\ArtistResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditArtist extends EditRecord
{
    protected static string $resource = ArtistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * If an image file was uploaded, derive image_url from the stored path
     * and strip the virtual field so Eloquent never sees it.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['image_file'])) {
            $data['image_url'] = Storage::disk('public')->url($data['image_file']);
        }

        unset($data['image_file']);

        return $data;
    }

    protected function afterSave(): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->record)
            ->withProperties(['image_url' => $this->record->image_url])
            ->log("Updated image for artist: {$this->record->name}");
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return "Image updated for '{$this->record->name}'";
    }
}
