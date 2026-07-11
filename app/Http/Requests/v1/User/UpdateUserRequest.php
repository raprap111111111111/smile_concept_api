<?php
// app/Http/Requests/v1/User/UpdateUserRequest.php

namespace App\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        // Allow user to update own profile even without update permission
        if ($user && $this->user()->id === $user->id) {
            return true;
        }
        return $user && $this->user()->can('update', $user);
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'email'     => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'password'  => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],

            // ✅ Photo upload
            'photo'     => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image'  => 'The file must be an image',
            'photo.mimes'  => 'Photo must be JPEG, PNG, or WebP',
            'photo.max'    => 'Photo size must be less than 5MB',
            'email.unique' => 'This email is already in use',
        ];
    }
}