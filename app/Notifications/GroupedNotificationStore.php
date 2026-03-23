<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class GroupedNotificationStore
{
    public function store(User $recipient, object $notification, string $groupKey): bool
    {
        if (!method_exists($notification, 'toDatabase')) {
            return false;
        }

        $baseData = $notification->toDatabase($recipient);

        $existing = DatabaseNotification::query()
            ->where('notifiable_type', $recipient->getMorphClass())
            ->where('notifiable_id', $recipient->getKey())
            ->whereNull('read_at')
            ->where('type', $notification::class)
            ->where('data->group_key', $groupKey)
            ->first();

        if ($existing) {
            $data = $existing->data ?? [];
            $data['count'] = ($data['count'] ?? 1) + 1;

            $actor = $baseData['actor'] ?? null;
            if ($actor) {
                $data['actors'] = collect($data['actors'] ?? [])
                    ->push($actor)
                    ->unique(fn ($a) => $a['id'] ?? null)
                    ->take(5)
                    ->values()
                    ->all();
            }

            $data['latest_at'] = now()->toIso8601String();

            $existing->update(['data' => $data]);

            return false;
        }

        $data = $baseData;
        $data['group_key'] = $groupKey;
        $data['count'] = 1;

        if (isset($baseData['actor'])) {
            $data['actors'] = [$baseData['actor']];
        }

        $data['latest_at'] = now()->toIso8601String();

        DatabaseNotification::create([
            'id'             => (string) Str::uuid(),
            'type'           => $notification::class,
            'notifiable_type' => $recipient->getMorphClass(),
            'notifiable_id'  => $recipient->getKey(),
            'data'           => $data,
        ]);

        return true;
    }
}
