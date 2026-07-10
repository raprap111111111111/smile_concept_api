<?php

namespace App\Http\Requests\v1\DentalChartEntry;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDentalChartEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entry = $this->route('dental_chart_entry');
        return $entry && $this->user()->can('delete', $entry);
    }

    public function rules(): array
    {
        return [];
    }
}
