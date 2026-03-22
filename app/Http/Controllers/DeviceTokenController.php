<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'in:ios,android'],
            'expo_version' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        $token = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $user->id,
                'platform' => $data['platform'] ?? null,
                'expo_version' => $data['expo_version'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return $this->success([
            'id' => $token->id,
            'token' => $token->token,
            'platform' => $token->platform,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();

        DeviceToken::where('user_id', $user->id)
            ->where('token', $data['token'])
            ->delete();

        return $this->success(null, 'Device token removed');
    }
}

