<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetCurrentVibeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:album,track'],
            'mbid' => ['nullable', 'string', 'max:36'],
        ];
    }
}
