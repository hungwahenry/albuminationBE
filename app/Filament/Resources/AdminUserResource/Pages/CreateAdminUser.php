<?php

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use App\Models\AdminUser;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function afterCreate(): void
    {
        $role = $this->data['roles'] ?? null;
        if ($role) {
            /** @var AdminUser $record */
            $record = $this->getRecord();
            $record->syncRoles([$role]);
        }

        activity()->causedBy(auth()->user())->performedOn($this->getRecord())
            ->log("Created admin account: {$this->getRecord()->email}");
    }
}
