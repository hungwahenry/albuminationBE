<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailChangeCodeRequest;
use App\Http\Requests\VerifyEmailChangeRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function sendEmailChangeCode(SendEmailChangeCodeRequest $request): JsonResponse
    {
        $this->authService->sendEmailChangeCode(
            $request->user(),
            $request->validated('email'),
        );

        return $this->success(message: 'Verification code sent to your new email address.');
    }

    public function verifyEmailChange(VerifyEmailChangeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->authService->verifyEmailChange(
            $request->user(),
            $validated['email'],
            $validated['code'],
        );

        if (! $result['valid']) {
            return $this->error($result['message'], 422);
        }

        return $this->success(message: 'Email updated successfully.');
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->delete();

        return $this->success(message: 'Account deleted.');
    }
}
