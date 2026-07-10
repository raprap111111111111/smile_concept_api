<?php

namespace App\Http\Requests\v1\Treatment;

use App\Models\Treatment;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Treatment::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:treatments,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.00'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
