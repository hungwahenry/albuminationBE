<?php

namespace App\Filament\Resources\TakeReplyResource\Pages;

use App\Filament\Resources\TakeReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTakeReplies extends ListRecords
{
    protected static string $resource = TakeReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // read-only
        ];
    }
}
