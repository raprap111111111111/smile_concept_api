<?php

namespace App\Http\Requests\v1\LabCase;

use Illuminate\Foundation\Http\FormRequest;

class DeleteLabCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $labCase = $this->route('lab_case');
        return $labCase && $this->user()->can('delete', $labCase);
    }

    public function rules(): array
    {
        return [];
    }
}
