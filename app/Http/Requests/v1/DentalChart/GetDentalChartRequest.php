<?php

namespace App\Http\Requests\v1\DentalChart;

use Illuminate\Foundation\Http\FormRequest;

class GetDentalChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dentalChart = $this->route('dental_chart');
        return $dentalChart && $this->user()->can('view', $dentalChart);
    }

    public function rules(): array
    {
        return [];
    }
}
