<?php

namespace App\Http\Requests\v1\Inventory;

use App\Http\Requests\Concerns\ChecksBranchAccess;
use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    use ChecksBranchAccess;

    public function authorize(): bool
    {
        // InventoryPolicy::create() cannot see the branch — it arrives in the
        // body, not the route — so the branch half of the check happens here.
        return $this->user()->can('create', Inventory::class)
            && $this->canAccessBranch($this->input('branch_id'));
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
