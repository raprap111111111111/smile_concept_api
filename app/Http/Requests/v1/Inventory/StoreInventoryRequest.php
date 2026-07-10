<?php

namespace App\Http\Requests\v1\Inventory;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Inventory::class);
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
