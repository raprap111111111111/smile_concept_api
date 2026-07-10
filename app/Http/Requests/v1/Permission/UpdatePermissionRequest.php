<?php

namespace App\Http\Requests\v1\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');
        return $permission && $this->user()->can('update', $permission);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:permissions,name,' . $this->route('permission')->id],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}