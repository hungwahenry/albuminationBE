<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:4', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:profiles,username'],
            'display_name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'place_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
        ];
    }
}
