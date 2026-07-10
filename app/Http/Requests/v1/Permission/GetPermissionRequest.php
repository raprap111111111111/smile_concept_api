<?php

namespace App\Http\Requests\v1\Permission;

use Illuminate\Foundation\Http\FormRequest;

class GetPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $permission && $this->user()->can('view', $permission);
    }

    public function rules(): array
    {
        return [];
    }
}