<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use App\Enums\TreatmentPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('treatment_plan');
        return $plan && $this->user()->can('update', $plan);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctors,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(TreatmentPlanStatus::class)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.treatment_id' => ['required_with:items', 'integer', 'exists:treatments,id'],
            'items.*.sequence_order' => ['required_with:items', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
