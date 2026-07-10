<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use App\Models\TreatmentPlan;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreatmentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', TreatmentPlan::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.treatment_id' => ['required', 'integer', 'exists:treatments,id'],
            'items.*.sequence_order' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
