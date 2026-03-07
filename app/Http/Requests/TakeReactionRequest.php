<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TakeReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:agree,disagree'],
        ];
    }
}
