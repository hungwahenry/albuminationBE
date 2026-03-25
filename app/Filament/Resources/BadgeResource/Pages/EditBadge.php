<?php

namespace App\Filament\Resources\BadgeResource\Pages;

use App\Filament\Resources\BadgeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditBadge extends EditRecord
{
    protected static string $resource = BadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => auth('admin')->user()?->can('badges.manage')),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['icon_file'])) {
            $data['icon'] = Storage::disk('public')->url($data['icon_file']);
        }

        unset($data['icon_file']);

        return $data;
    }

    protected function afterSave(): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->record)
            ->withProperties(['name' => $this->record->name, 'rarity' => $this->record->rarity])
            ->log("Updated badge: {$this->record->name}");
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return "Badge '{$this->record->name}' updated";
    }
}
