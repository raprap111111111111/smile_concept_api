<?php

namespace App\Http\Requests\v1\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Permission;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Permission::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}