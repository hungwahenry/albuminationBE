<?php

namespace App\Services;

use App\Mail\LoginCodeMail;
use App\Mail\SignupCodeMail;
use App\Models\MagicCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    private const MAX_ATTEMPTS = 5;

    public function sendMagicCode(string $email): array
    {
        $userExists = User::where('email', $email)->exists();
        $type = $userExists ? 'login' : 'signup';

        // Invalidate any existing codes for this email
        MagicCode::where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        MagicCode::create([
            'email' => $email,
            'code' => Hash::make($code),
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        $mailable = $type === 'login'
            ? new LoginCodeMail($code)
            : new SignupCodeMail($code);

        Mail::to($email)->send($mailable);

        return ['type' => $type];
    }

    public function verifyMagicCode(string $email, string $code): array
    {
        $magicCode = MagicCode::where('email', $email)->first();

        if (!$magicCode || $magicCode->isExpired()) {
            return ['valid' => false, 'message' => 'Invalid or expired code.'];
        }

        if ($magicCode->attempts >= self::MAX_ATTEMPTS) {
            $magicCode->delete();

            return ['valid' => false, 'message' => 'Too many failed attempts. Please request a new code.'];
        }

        if (!Hash::check($code, $magicCode->code)) {
            $magicCode->increment('attempts');

            return ['valid' => false, 'message' => 'Invalid or expired code.'];
        }

        $isSignup = $magicCode->type === 'signup';

        if ($isSignup) {
            $user = User::create([
                'email' => $email,
                'email_verified_at' => now(),
            ]);
        } else {
            $user = User::where('email', $email)->firstOrFail();
        }

        // Clean up used code
        MagicCode::where('email', $email)->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'valid' => true,
            'user' => $user,
            'token' => $token,
            'is_new_user' => $isSignup,
        ];
    }
}
