<?php

namespace App\Http\Requests\v1\DentalChartEntry;

use App\Models\DentalChartEntry;
use Illuminate\Foundation\Http\FormRequest;

class StoreDentalChartEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DentalChartEntry::class);
    }

    public function rules(): array
    {
        return [
            'dental_chart_id' => ['required', 'integer', 'exists:dental_charts,id'],
            'tooth_number' => ['required', 'string', 'max:5'],
            'tooth_condition_id' => ['required', 'integer', 'exists:tooth_conditions,id'],
            'treatment_applied' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
