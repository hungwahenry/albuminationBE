<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRotationItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mbid' => ['required', 'uuid'],
        ];
    }
}
