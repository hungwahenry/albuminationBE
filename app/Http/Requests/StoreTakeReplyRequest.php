<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTakeReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body'              => ['nullable', 'string', 'max:500'],
            'gif_url'           => ['nullable', 'url', 'max:500'],
            'reply_to_username' => ['nullable', 'string', 'exists:profiles,username'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->body) && empty($this->gif_url)) {
                $v->errors()->add('body', 'A reply must have text or a GIF.');
            }
        });
    }
}
