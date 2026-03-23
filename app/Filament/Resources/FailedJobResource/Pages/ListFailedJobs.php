<?php

namespace App\Filament\Resources\FailedJobResource\Pages;

use App\Filament\Resources\FailedJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('retry_all')
                ->label('Retry All')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will re-queue every failed job. Are you sure?')
                ->visible(fn () => auth('admin')->user()?->can('system.queue') && \App\Models\FailedJob::count() > 0)
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('queue:retry', ['id' => ['all']]);
                    activity()->causedBy(auth()->user())->log('Retried all failed jobs');
                    \Filament\Notifications\Notification::make()->title('All failed jobs queued for retry')->success()->send();
                }),
        ];
    }
}
