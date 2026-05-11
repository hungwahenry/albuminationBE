<?php

namespace App\Rules;

use App\Services\ContentModerationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class PassesModeration implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '') {
            return;
        }

        $result = app(ContentModerationService::class)->check($value);

        if ($result->allowed) {
            return;
        }

        activity('moderation')
            ->causedBy(Auth::user())
            ->withProperties([
                'action'   => 'content_blocked',
                'field'    => $attribute,
                'category' => $result->flaggedCategory,
                'score'    => $result->flaggedScore,
                'excerpt'  => mb_substr($value, 0, 200),
            ])
            ->log('Content blocked by moderation');

        $fail("This couldn't be posted because it violates our community guidelines.");
    }
}
