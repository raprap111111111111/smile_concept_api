<?php

namespace App\Http\Requests\v1\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class GetTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $treatment = $this->route('treatment');
        return $treatment && $this->user()->can('view', $treatment);
    }

    public function rules(): array
    {
        return [];
    }
}
