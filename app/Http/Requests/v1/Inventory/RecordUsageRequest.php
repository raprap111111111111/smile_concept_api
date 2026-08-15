<?php

namespace App\Http\Requests\v1\Inventory;

use App\Http\Requests\Concerns\ChecksBranchAccess;
use Illuminate\Foundation\Http\FormRequest;

class RecordUsageRequest extends FormRequest
{
    use ChecksBranchAccess;

    public function authorize(): bool
    {
        return $this->user()->can('inventory.stock-out')
            && $this->canAccessBranch($this->input('branch_id'));
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'item_id'   => ['required', 'integer', 'exists:items,id'],
            'quantity'  => ['required', 'integer', 'min:1'],
            'reason'    => ['nullable', 'string', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ];
    }
}
