<?php

namespace App\Http\Requests\v1\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');
        return $role && $this->user()->can('update', $role);
    }

    public function rules(): array
    {
        return [
            'name' => 
            [
                'sometimes', 
                'required', 
                'string', 
                'max:255', 
                'unique:roles,name,' . 
                $this->route('role')->id
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}