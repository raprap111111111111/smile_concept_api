<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use App\Enums\TreatmentPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTreatmentPlanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user          = $this->user();
        $treatmentPlan = $this->route('treatmentPlan');
        $targetStatus  = TreatmentPlanStatus::tryFrom($this->input('status'));

        if (!$user || !$treatmentPlan || !$targetStatus) {
            return false;
        }

        // 1️⃣ Check if the transition is allowed by the enum logic
        if (!$treatmentPlan->status->canTransitionTo($targetStatus)) {
            return false;
        }

        // 2️⃣ Map target status → required permission
        $permission = match ($targetStatus) {
            TreatmentPlanStatus::PROPOSED  => 'treatment-plan.send-to-patient',
            TreatmentPlanStatus::ACCEPTED  => 'treatment-plan.accept',
            TreatmentPlanStatus::REJECTED  => 'treatment-plan.reject',
            TreatmentPlanStatus::COMPLETED => 'treatment-plan.mark-completed',
            TreatmentPlanStatus::DRAFT     => 'treatment-plan.reopen',  // ← reopen
        };

        return $user->can($permission);
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(TreatmentPlanStatus::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A target status is required.',
            'status.enum'     => 'Status must be one of: '
                . implode(', ', array_column(TreatmentPlanStatus::cases(), 'value')),
        ];
    }
}