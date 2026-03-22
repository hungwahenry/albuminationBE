<?php

namespace App\Filament\Resources\RotationCommentResource\Pages;

use App\Filament\Resources\RotationCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRotationComments extends ListRecords
{
    protected static string $resource = RotationCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // read-only
        ];
    }
}
