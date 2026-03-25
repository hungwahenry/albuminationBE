<?php

namespace App\Filament\Resources\BadgeResource\Pages;

use App\Filament\Resources\BadgeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBadge extends ViewRecord
{
    protected static string $resource = BadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => auth('admin')->user()?->can('badges.manage')),
        ];
    }
}
