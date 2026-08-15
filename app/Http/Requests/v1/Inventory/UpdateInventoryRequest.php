<?php

namespace App\Http\Requests\v1\Inventory;

use App\Http\Requests\Concerns\ChecksBranchAccess;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    use ChecksBranchAccess;

    public function authorize(): bool
    {
        $inventory = $this->route('inventory');

        if (! $inventory || ! $this->user()->can('update', $inventory)) {
            return false;
        }

        // The policy covers the branch the row is in today. This covers where
        // it is being moved TO — without it, someone could re-point a row they
        // legitimately control into a branch they do not.
        if ($this->has('branch_id')) {
            return $this->canAccessBranch($this->input('branch_id'));
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['sometimes', 'required', 'integer', 'exists:branches,id'],
            'item_id' => ['sometimes', 'required', 'integer', 'exists:items,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'expiry_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
