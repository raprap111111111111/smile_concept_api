<?php

namespace App\Http\Requests\v1\TreatmentPlan;

use App\Http\Requests\Concerns\ChecksBranchAccess;
use Illuminate\Foundation\Http\FormRequest;

class RecordTreatmentPlanConsumablesRequest extends FormRequest
{
    use ChecksBranchAccess;

    public function authorize(): bool
    {
        return $this->user()->can('treatment-plan.record-consumables')
            && $this->canAccessBranch($this->input('branch_id'));
    }

    public function rules(): array
    {
        return [
            // Existence and membership are the branch check's job — a foreign
            // or nonexistent branch is a 403, not a 422.
            'branch_id'        => ['required', 'integer'],
            'lines'            => ['required', 'array', 'min:1'],
            'lines.*.item_id'  => ['required', 'integer', 'distinct', 'exists:items,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.*.item_id.distinct' => 'Each supply may only appear once — combine duplicate lines.',
        ];
    }
}
