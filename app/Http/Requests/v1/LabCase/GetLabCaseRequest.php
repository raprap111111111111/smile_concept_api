<?php

namespace App\Http\Requests\v1\LabCase;

use Illuminate\Foundation\Http\FormRequest;

class GetLabCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $labCase = $this->route('lab_case');
        return $labCase && $this->user()->can('view', $labCase);
    }

    public function rules(): array
    {
        return [];
    }
}
