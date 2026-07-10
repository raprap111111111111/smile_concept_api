<?php

namespace App\Http\Requests\v1\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inventory = $this->route('inventory');
        return $inventory && $this->user()->can('update', $inventory);
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
