<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use Illuminate\Foundation\Http\FormRequest;

class GetTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('treatment_plan');
        return $plan && $this->user()->can('view', $plan);
    }

    public function rules(): array
    {
        return [];
    }
}
