<?php

namespace App\Services;

use App\Mail\AccountDeletionCodeMail;
use App\Mail\EmailChangeCodeMail;
use App\Mail\LoginCodeMail;
use App\Mail\SignupCodeMail;
use App\Models\MagicCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        return DB::transaction(function () use ($email, $magicCode): array {
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
        });
    }

    public function sendEmailChangeCode(User $user, string $newEmail): void
    {
        // Invalidate any existing email_change codes for this address
        MagicCode::where('email', $newEmail)->where('type', 'email_change')->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        MagicCode::create([
            'email'      => $newEmail,
            'code'       => Hash::make($code),
            'type'       => 'email_change',
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($newEmail)->send(new EmailChangeCodeMail($code));
    }

    public function sendAccountDeletionCode(User $user): void
    {
        MagicCode::where('email', $user->email)->where('type', 'account_deletion')->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        MagicCode::create([
            'email'      => $user->email,
            'code'       => Hash::make($code),
            'type'       => 'account_deletion',
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new AccountDeletionCodeMail($code));
    }

    public function confirmAccountDeletion(User $user, string $code): array
    {
        $magicCode = MagicCode::where('email', $user->email)
            ->where('type', 'account_deletion')
            ->first();

        if (! $magicCode || $magicCode->isExpired()) {
            return ['valid' => false, 'message' => 'Invalid or expired code.'];
        }

        if ($magicCode->attempts >= self::MAX_ATTEMPTS) {
            $magicCode->delete();
            return ['valid' => false, 'message' => 'Too many failed attempts. Please request a new code.'];
        }

        if (! Hash::check($code, $magicCode->code)) {
            $magicCode->increment('attempts');
            return ['valid' => false, 'message' => 'Invalid code.'];
        }

        $magicCode->delete();

        return ['valid' => true];
    }

    public function verifyEmailChange(User $user, string $newEmail, string $code): array
    {
        $magicCode = MagicCode::where('email', $newEmail)
            ->where('type', 'email_change')
            ->first();

        if (! $magicCode || $magicCode->isExpired()) {
            return ['valid' => false, 'message' => 'Invalid or expired code.'];
        }

        if ($magicCode->attempts >= self::MAX_ATTEMPTS) {
            $magicCode->delete();
            return ['valid' => false, 'message' => 'Too many failed attempts. Please request a new code.'];
        }

        if (! Hash::check($code, $magicCode->code)) {
            $magicCode->increment('attempts');
            return ['valid' => false, 'message' => 'Invalid code.'];
        }

        $magicCode->delete();

        $user->email = $newEmail;
        $user->email_verified_at = now();
        $user->save();

        return ['valid' => true];
    }
}
