<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Powers the recording dialog only — suggested quantities plus whatever was
 * already recorded — so it is gated on the same permission as recording.
 */
class GetTreatmentPlanConsumablesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('treatment-plan.record-consumables');
    }

    public function rules(): array
    {
        return [];
    }
}
