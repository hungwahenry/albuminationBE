<?php

namespace App\Filament\Resources\DeviceTokenResource\Pages;

use App\Filament\Resources\DeviceTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceTokens extends ListRecords
{
    protected static string $resource = DeviceTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('prune_all_stale')
                ->label('Prune All Stale')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Delete all device tokens not used in 90+ days (and registered 90+ days ago).')
                ->visible(fn () => auth()->user()->can('device_tokens.manage'))
                ->action(function () {
                    $count = \App\Models\DeviceToken::where(function ($q) {
                        $q->whereNull('last_used_at')
                          ->orWhere('last_used_at', '<', now()->subDays(90));
                    })->where('created_at', '<', now()->subDays(90))->delete();
                    activity()->causedBy(auth()->user())->log("Pruned {$count} stale device tokens");
                    \Filament\Notifications\Notification::make()->title("{$count} stale tokens pruned")->success()->send();
                }),
        ];
    }
}
