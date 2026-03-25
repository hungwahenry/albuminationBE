<?php

namespace App\Filament\Resources\BadgeRarityResource\Pages;

use App\Filament\Resources\BadgeRarityResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditBadgeRarity extends EditRecord
{
    protected static string $resource = BadgeRarityResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make()];
    }

    protected function afterSave(): void
    {
        Cache::forget("badge_rarity:{$this->record->key}");

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->record)
            ->withProperties(['key' => $this->record->key])
            ->log("Updated badge rarity config: {$this->record->key}");
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return "Rarity '{$this->record->label}' updated";
    }
}
