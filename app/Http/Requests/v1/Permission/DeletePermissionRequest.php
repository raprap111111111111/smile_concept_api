<?php

namespace App\Http\Requests\v1\Permission;

use Illuminate\Foundation\Http\FormRequest;

class DeletePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $permission && $this->user()->can('delete', $permission);
    }

    public function rules(): array
    {
        return [];
    }
}