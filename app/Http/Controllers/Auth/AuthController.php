<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteOnboardingRequest;
use App\Http\Requests\Auth\SendMagicCodeRequest;
use App\Http\Requests\Auth\VerifyMagicCodeRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function sendCode(SendMagicCodeRequest $request): JsonResponse
    {
        $result = $this->authService->sendMagicCode($request->validated('email'));

        return $this->success(
            ['type' => $result['type']],
            'Magic code sent to your email.',
        );
    }

    public function verifyCode(VerifyMagicCodeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->authService->verifyMagicCode($validated['email'], $validated['code']);

        if (!$result['valid']) {
            return $this->error($result['message'], 422);
        }

        return $this->success([
            'user' => new UserResource($result['user']->load('profile')),
            'token' => $result['token'],
            'is_new_user' => $result['is_new_user'],
        ], 'Authentication successful.');
    }

    public function completeOnboarding(CompleteOnboardingRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->profile()->create($validated);
        $user->update(['onboarding_completed_at' => now()]);

        return $this->success(
            new UserResource($user->load('profile')),
            'Onboarding completed.',
            201,
        );
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()->load('profile')),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(message: 'Logged out successfully.');
    }
}
