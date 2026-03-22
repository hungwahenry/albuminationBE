<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Hide the save button entirely for super_admin — the form is read-only.
     */
    protected function getFormActions(): array
    {
        if ($this->record->name === 'super_admin') {
            return [];
        }

        return parent::getFormActions();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return "Permissions updated for '{$this->record->name}'";
    }

    protected function afterSave(): void
    {
        $permissions = $this->record->permissions->pluck('name')->sort()->implode(', ');

        activity()
            ->causedBy(auth()->user())
            ->performedOn($this->record)
            ->withProperties(['permissions' => $permissions])
            ->log("Updated permissions for role: {$this->record->name}");
    }
}
