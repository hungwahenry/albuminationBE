<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_follower_in_app'       => ['sometimes', 'boolean'],
            'new_follower_push'         => ['sometimes', 'boolean'],
            'new_follower_mail'         => ['sometimes', 'boolean'],
            'like_content_in_app'       => ['sometimes', 'boolean'],
            'like_content_push'         => ['sometimes', 'boolean'],
            'like_content_mail'         => ['sometimes', 'boolean'],
            'comment_content_in_app'    => ['sometimes', 'boolean'],
            'comment_content_push'      => ['sometimes', 'boolean'],
            'comment_content_mail'      => ['sometimes', 'boolean'],
            'reply_content_in_app'      => ['sometimes', 'boolean'],
            'reply_content_push'        => ['sometimes', 'boolean'],
            'reply_content_mail'        => ['sometimes', 'boolean'],
            'rotation_published_in_app' => ['sometimes', 'boolean'],
            'rotation_published_push'   => ['sometimes', 'boolean'],
            'rotation_published_mail'   => ['sometimes', 'boolean'],
            'report_updates_in_app'     => ['sometimes', 'boolean'],
            'report_updates_push'       => ['sometimes', 'boolean'],
            'report_updates_mail'       => ['sometimes', 'boolean'],
        ];
    }
}
