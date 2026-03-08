<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileId = $this->user()->profile->id;

        return [
            'display_name'      => ['sometimes', 'string', 'max:255'],
            'username'          => ['sometimes', 'string', 'min:4', 'max:32', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('profiles')->ignore($profileId)],
            'bio'               => ['nullable', 'string', 'max:500'],
            'avatar'            => ['nullable', 'image', 'max:2048'],
            'header_album_id'   => ['nullable', 'integer', 'exists:albums,id'],
            'pinned_rotation_id'=> ['nullable', 'integer'],
            'current_vibe_type' => ['nullable', 'string', 'in:album,track'],
            'current_vibe_id'   => ['nullable', 'integer', 'required_with:current_vibe_type'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username may only contain letters, numbers, and underscores.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePinnedRotation($validator);
            $this->validateCurrentVibe($validator);
        });
    }

    private function validatePinnedRotation($validator): void
    {
        $rotationId = $this->input('pinned_rotation_id');
        if (!$rotationId) return;

        $rotation = \App\Models\Rotation::find($rotationId);
        if (!$rotation || $rotation->user_id !== $this->user()->id) {
            $validator->errors()->add('pinned_rotation_id', 'You can only pin your own rotations.');
            return;
        }
        if ($rotation->status !== 'published') {
            $validator->errors()->add('pinned_rotation_id', 'Only published rotations can be pinned.');
        }
    }

    private function validateCurrentVibe($validator): void
    {
        $type = $this->input('current_vibe_type');
        $id = $this->input('current_vibe_id');

        if (!$type || !$id) return;

        $model = $type === 'album' ? \App\Models\Album::class : \App\Models\Track::class;
        if (!$model::where('id', $id)->exists()) {
            $validator->errors()->add('current_vibe_id', "The selected {$type} does not exist.");
        }
    }
}
