<?php

namespace App\Http\Requests\v1\Treatment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $treatment = $this->route('treatment');
        return $treatment && $this->user()->can('update', $treatment);
    }

    public function rules(): array
    {
        $treatmentId = $this->route('treatment')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', "unique:treatments,name,{$treatmentId}"],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0.00'],
            'estimated_duration_minutes' => ['sometimes', 'required', 'integer', 'min:5'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
