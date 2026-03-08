<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:100'],
            'caption'     => ['nullable', 'string', 'max:500'],
            'type'        => ['required', 'in:album,track'],
            'is_ranked'   => ['sometimes', 'boolean'],
            'is_public'   => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'vibetags'    => ['sometimes', 'array', 'max:10'],
            'vibetags.*'  => ['string', 'max:30'],
        ];
    }
}
