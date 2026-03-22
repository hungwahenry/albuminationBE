<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetHeaderAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mbid' => ['nullable', 'string', 'max:36'],
        ];
    }
}
