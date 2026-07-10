<?php

namespace App\Http\Requests\v1\Branch;

use Illuminate\Foundation\Http\FormRequest;

class DeleteBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branch = $this->route('branch');

        return $branch && $this->user()->can('delete', $branch);
    }

    public function rules(): array
    {
        return [];
    }
}