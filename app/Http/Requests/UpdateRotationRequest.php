<?php

namespace App\Http\Requests;

use App\Rules\PassesModeration;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:100', new PassesModeration()],
            'caption'     => ['nullable', 'string', 'max:500', new PassesModeration()],
            'is_ranked'   => ['sometimes', 'boolean'],
            'is_public'   => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'vibetags'    => ['sometimes', 'array', 'max:10'],
            'vibetags.*'  => ['string', 'max:30'],
        ];
    }
}
