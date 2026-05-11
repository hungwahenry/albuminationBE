<?php

namespace App\Filament\Resources\ModerationSettingResource\Pages;

use App\Filament\Resources\ModerationSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditModerationSetting extends EditRecord
{
    protected static string $resource = ModerationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        activity()
            ->causedBy(auth('admin')->user())
            ->performedOn($this->record)
            ->withProperties(['changes' => $this->record->getChanges()])
            ->log('Updated moderation settings');
    }
}
