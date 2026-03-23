<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class ScheduledTasks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Scheduled Tasks';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.scheduled-tasks';

    public array $tasks = [];

    public function mount(): void
    {
        abort_unless(auth('admin')->user()?->can('system.schedule'), 403);
        $this->loadTasks();
    }

    public function loadTasks(): void
    {
        /** @var Schedule $schedule */
        $schedule = app(Schedule::class);

        $this->tasks = collect($schedule->events())
            ->map(fn ($event) => [
                'command'     => $event->command ?? $event->description ?? 'Closure',
                'expression'  => $event->expression,
                'description' => $event->description ?? '',
                'timezone'    => $event->timezone ?? config('app.timezone'),
                'next_due'    => $event->nextRunDate()->format('Y-m-d H:i:s'),
                'mutex'       => $event->mutexName(),
            ])
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_scheduler')
                ->label('Run Scheduler Now')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This runs php artisan schedule:run immediately. Only due tasks will execute.')
                ->visible(fn () => auth('admin')->user()?->can('system.schedule'))
                ->action(function () {
                    Artisan::call('schedule:run');
                    $output = trim(Artisan::output());
                    activity()->causedBy(auth()->user())
                        ->log('Manually triggered schedule:run');
                    Notification::make()
                        ->title('Scheduler run complete')
                        ->body($output ?: 'No tasks were due.')
                        ->success()
                        ->send();
                    $this->loadTasks();
                }),
        ];
    }
}
