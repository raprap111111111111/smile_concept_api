<?php

namespace App\Http\Requests\v1\Branch;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Branch::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'branch_code' => ['nullable', 'string', 'max:50', 'unique:branches,branch_code'],
            'address'     => ['required', 'string', 'max:255'],
            'city'        => ['nullable', 'string', 'max:100'],
            'province'    => ['nullable', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'email'       => ['nullable', 'email', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'opening_hours' => ['nullable', 'string', 'max:100'],
        ];
    }
}
