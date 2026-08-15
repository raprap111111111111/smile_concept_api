<?php

namespace App\Http\Requests\v1\Inventory;

use App\Http\Requests\Concerns\ChecksBranchAccess;
use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    use ChecksBranchAccess;

    public function authorize(): bool
    {
        // Both ends, deliberately. Requiring only the source would let someone
        // push stock into a branch they have nothing to do with; requiring only
        // the destination would let them drain one. In practice transfers are
        // done by staff who work at both, or by an admin.
        return $this->user()->can('inventory.transfer')
            && $this->canAccessBranch($this->input('from_branch_id'))
            && $this->canAccessBranch($this->input('to_branch_id'));
    }

    public function rules(): array
    {
        return [
            'from_branch_id' => ['required', 'integer', 'exists:branches,id'],
            'to_branch_id'   => ['required', 'integer', 'exists:branches,id', 'different:from_branch_id'],
            'item_id'        => ['required', 'integer', 'exists:items,id'],
            'quantity'       => ['required', 'integer', 'min:1'],
            'reason'         => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_branch_id.different' => 'Pick a different destination branch — stock cannot transfer to itself.',
        ];
    }
}
