<?php

namespace App\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            // Staff accounts must land with a role, or they log in with zero
            // permissions. Block `patient` — that role is for the self-service
            // portal and is assigned by the registration flow, not here.
            'role' => [
                'required',
                'string',
                'exists:roles,name',
                'not_in:patient',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        // Only a super-admin may mint another super-admin. Without this an
        // ordinary admin (who holds `user.create`) could escalate by handing
        // out the top-level role.
        $validator->after(function ($validator) {
            if (
                $this->input('role') === 'super-admin'
                && ! $this->user()->hasRole('super-admin')
            ) {
                $validator->errors()->add(
                    'role',
                    'You are not allowed to assign the super-admin role.'
                );
            }
        });
    }
}