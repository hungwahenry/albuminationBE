<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GiphySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'      => ['required', 'string', 'min:1', 'max:100'],
            'limit'  => ['sometimes', 'integer', 'min:1', 'max:50'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
