<?php

namespace App\Filament\Resources\VibetagResource\Pages;

use App\Filament\Resources\VibetagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVibetags extends ListRecords
{
    protected static string $resource = VibetagResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
