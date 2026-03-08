<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->user()->profile->id;

        return [
            'display_name' => ['sometimes', 'string', 'max:255'],
            'username'     => ['sometimes', 'string', 'min:4', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('profiles')->ignore($profileId)],
            'bio'          => ['nullable', 'string', 'max:500'],
            'avatar'       => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
        ];
    }
}
