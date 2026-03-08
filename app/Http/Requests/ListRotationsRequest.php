<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListRotationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => ['sometimes', 'in:draft,published'],
            'type'          => ['sometimes', 'in:album,track'],
            'sort'          => ['sometimes', 'in:newest,oldest,alphabetical,recently_updated'],
            'contains_mbid' => ['sometimes', 'string', 'max:40'],
        ];
    }
}
