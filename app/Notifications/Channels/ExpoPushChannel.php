<?php

namespace App\Notifications\Channels;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!$notifiable instanceof User) {
            return;
        }

        $tokens = $notifiable->deviceTokens()->pluck('token')->all();

        if (empty($tokens)) {
            return;
        }

        $payload = method_exists($notification, 'toDatabase')
            ? $notification->toDatabase($notifiable)
            : [];

        $title = $payload['title'] ?? 'Albumination';
        $actorName = $payload['actor']['display_name']
            ?? $payload['actor']['username']
            ?? null;
        $message = $payload['message'] ?? '';
        $body = $payload['body'] ?? ($actorName && $message ? "{$actorName} {$message}" : $message);
        $data = $payload['data'] ?? $payload;

        if ($body === '') {
            return;
        }

        foreach (array_chunk($tokens, 100) as $chunk) {
            $messages = [];

            foreach ($chunk as $token) {
                $messages[] = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'data' => $data,
                ];
            }

            $response = Http::post(config('services.expo.push_url'), $messages);

            Log::channel('stack')->info('ExpoPush sent', [
                'tokens' => $chunk,
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            $this->pruneInvalidTokens($chunk, $response->json('data', []));
        }
    }

    private function pruneInvalidTokens(array $chunk, array $results): void
    {
        $invalidTokens = [];

        foreach ($results as $index => $result) {
            if (
                ($result['status'] ?? '') === 'error' &&
                ($result['details']['error'] ?? '') === 'DeviceNotRegistered'
            ) {
                $invalidTokens[] = $chunk[$index];
            }
        }

        if (!empty($invalidTokens)) {
            DeviceToken::whereIn('token', $invalidTokens)->delete();

            Log::channel('stack')->info('ExpoPush pruned invalid tokens', [
                'tokens' => $invalidTokens,
            ]);
        }
    }
}

