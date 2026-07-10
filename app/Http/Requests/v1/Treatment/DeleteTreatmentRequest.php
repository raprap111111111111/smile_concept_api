<?php

namespace App\Http\Requests\v1\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $treatment = $this->route('treatment');
        return $treatment && $this->user()->can('delete', $treatment);
    }

    public function rules(): array
    {
        return [];
    }
}
