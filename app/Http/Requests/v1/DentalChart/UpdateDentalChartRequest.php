<?php

namespace App\Http\Requests\v1\DentalChart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDentalChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dentalChart = $this->route('dental_chart');
        return $dentalChart && $this->user()->can('update', $dentalChart);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'appointment_id' => ['sometimes', 'nullable', 'integer', 'exists:appointments,id'],
            'general_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            
            'entries' => ['sometimes', 'required', 'array', 'min:1'],
            'entries.*.tooth_number' => ['required_with:entries', 'string', 'max:5'],
            'entries.*.tooth_condition_id' => ['required_with:entries', 'integer', 'exists:tooth_conditions,id'],
            'entries.*.treatment_applied' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
