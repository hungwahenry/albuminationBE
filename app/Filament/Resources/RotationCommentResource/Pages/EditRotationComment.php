<?php

namespace App\Filament\Resources\RotationCommentResource\Pages;

use App\Filament\Resources\RotationCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRotationComment extends EditRecord
{
    protected static string $resource = RotationCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
