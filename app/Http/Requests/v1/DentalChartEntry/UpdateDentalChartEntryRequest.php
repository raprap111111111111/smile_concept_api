<?php

namespace App\Http\Requests\v1\DentalChartEntry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDentalChartEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('dental_chart_entry');
        return $entry && $this->user()->can('update', $entry);
    }

    public function rules(): array
    {
        return [
            'dental_chart_id' => ['sometimes', 'required', 'integer', 'exists:dental_charts,id'],
            'tooth_number' => ['sometimes', 'required', 'string', 'max:5'],
            'tooth_condition_id' => ['sometimes', 'required', 'integer', 'exists:tooth_conditions,id'],
            'treatment_applied' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
