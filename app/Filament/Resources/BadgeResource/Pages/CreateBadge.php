<?php

namespace App\Filament\Resources\BadgeResource\Pages;

use App\Filament\Resources\BadgeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateBadge extends CreateRecord
{
    protected static string $resource = BadgeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['icon_file'])) {
            $data['icon'] = Storage::disk('public')->url($data['icon_file']);
        }

        unset($data['icon_file']);

        return $data;
    }

    protected function afterCreate(): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->record)
            ->log("Created badge: {$this->record->name}");
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return "Badge '{$this->record->name}' created";
    }
}
