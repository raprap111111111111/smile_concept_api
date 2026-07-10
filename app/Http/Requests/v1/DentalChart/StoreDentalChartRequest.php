<?php

namespace App\Http\Requests\v1\DentalChart;

use App\Models\DentalChart;
use Illuminate\Foundation\Http\FormRequest;

class StoreDentalChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('dental_charts.create');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'general_notes' => ['nullable', 'string', 'max:1000'],
            
            // Nested Child Entries Validation
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.tooth_number' => ['required', 'string', 'max:5'],
            'entries.*.tooth_condition_id' => ['required', 'integer', 'exists:tooth_conditions,id'],
            'entries.*.treatment_applied' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
