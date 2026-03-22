<?php

namespace App\Filament\Resources\TakeReplyResource\Pages;

use App\Filament\Resources\TakeReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTakeReply extends EditRecord
{
    protected static string $resource = TakeReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
