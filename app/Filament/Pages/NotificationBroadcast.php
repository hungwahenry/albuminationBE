<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;

class NotificationBroadcast extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Broadcast Notification';
    protected static ?int $navigationSort = 6;
    protected static string $view = 'filament.pages.notification-broadcast';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('notifications.manage'), 403);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(100),

                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->maxLength(500)
                    ->rows(3),

                Radio::make('audience')
                    ->label('Send To')
                    ->options([
                        'all'         => 'All users',
                        'onboarded'   => 'Onboarded users only',
                        'has_content' => 'Users who have created content',
                    ])
                    ->default('onboarded')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $query = User::query();

        match ($data['audience']) {
            'onboarded'   => $query->whereNotNull('onboarding_completed_at'),
            'has_content' => $query->where(fn ($q) => $q->whereHas('takes')->orWhereHas('rotations')),
            default       => null,
        };

        $count = $query->count();

        $payload = [
            'type'    => 'broadcast',
            'title'   => $data['title'],
            'message' => $data['body'],
            'sent_by' => auth()->user()->email,
        ];

        // Insert database notifications in chunks to avoid memory issues
        $query->chunkById(200, function ($users) use ($payload) {
            $now   = now();
            $rows  = $users->map(fn (User $u) => [
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'broadcast',
                'notifiable_type' => User::class,
                'notifiable_id'   => $u->id,
                'data'            => json_encode($payload),
                'created_at'      => $now,
                'updated_at'      => $now,
            ])->toArray();

            \Illuminate\Support\Facades\DB::table('notifications')->insert($rows);
        });

        activity()->causedBy(auth()->user())
            ->withProperties(['audience' => $data['audience'], 'recipients' => $count, 'title' => $data['title']])
            ->log("Sent broadcast notification to {$count} users: {$data['title']}");

        $this->form->fill();

        Notification::make()
            ->title("Broadcast sent to {$count} users")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Broadcast')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('This will send a notification to all users in the selected audience. This cannot be undone.')
                ->action('send'),
        ];
    }
}
