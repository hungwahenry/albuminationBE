<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Events\ReportResolved;
use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_reviewed')
                ->label('Mark Reviewed')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn () => $this->record->status === 'pending' && auth()->user()->can('reports.action'))
                ->action(function () {
                    $this->record->update(['status' => 'reviewed']);
                    activity()->causedBy(auth()->user())->performedOn($this->record)->log('Marked report as reviewed');
                    Notification::make()->title('Report marked as reviewed')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('action_report')
                ->label('Action')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('This will notify the reporter that their report has been actioned.')
                ->visible(fn () => in_array($this->record->status, ['pending', 'reviewed']) && auth()->user()->can('reports.action'))
                ->action(function () {
                    $this->record->update(['status' => 'actioned']);
                    ReportResolved::dispatch($this->record, 'resolved');
                    activity()->causedBy(auth()->user())->performedOn($this->record)->log('Actioned report — reporter notified');
                    Notification::make()->title('Report actioned and reporter notified')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('dismiss')
                ->label('Dismiss')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['pending', 'reviewed']) && auth()->user()->can('reports.action'))
                ->action(function () {
                    $this->record->update(['status' => 'dismissed']);
                    ReportResolved::dispatch($this->record, 'dismissed');
                    activity()->causedBy(auth()->user())->performedOn($this->record)->log('Dismissed report — reporter notified');
                    Notification::make()->title('Report dismissed')->success()->send();
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
