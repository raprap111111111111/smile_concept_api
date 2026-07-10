<?php

namespace App\Http\Requests\v1\Branch;

use Illuminate\Foundation\Http\FormRequest;

class GetBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branch = $this->route('branch');

        return $branch && $this->user()->can('view', $branch);
    }

    public function rules(): array
    {
        return [];
    }
}