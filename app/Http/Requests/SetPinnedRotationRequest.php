<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPinnedRotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rotation_id' => ['nullable', 'integer'],
        ];
    }
}
