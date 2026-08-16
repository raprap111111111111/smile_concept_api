<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('treatment_plan');
        return $plan && $this->user()->can('delete', $plan);
    }

    public function rules(): array
    {
        return [];
    }
}
