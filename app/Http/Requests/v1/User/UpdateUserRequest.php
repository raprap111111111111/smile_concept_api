<?php

namespace App\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $user && $this->user()->can('update', $user);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $this->route('user')->id],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'branch_id' => ['sometimes', 'nullable', 'integer', 'exists:branches,id'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}