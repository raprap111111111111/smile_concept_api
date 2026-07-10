<?php

namespace App\Http\Requests\v1\Branch;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $branch = $this->route('branch');

        return $branch && $this->user()->can('update', $branch);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'branch_code'   => ['sometimes', 'string', 'max:50'],
            'address'       => ['sometimes', 'required', 'string', 'max:255'],
            'city'          => ['sometimes', 'nullable', 'string', 'max:100'],
            'province'      => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone'         => ['sometimes', 'nullable', 'string', 'max:20'],
            'email'         => ['sometimes', 'nullable', 'email', 'max:255'],
            'is_active'     => ['sometimes', 'boolean'],
            'opening_hours' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
