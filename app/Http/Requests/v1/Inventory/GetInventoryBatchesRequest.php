<?php

namespace App\Http\Requests\v1\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetInventoryBatchesRequest extends FormRequest
{
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        return $this->user()->can('inventory.viewAny');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'item_id'   => ['nullable', 'integer', 'exists:items,id'],
            'open_only' => ['nullable', 'boolean'],
            'search'    => ['nullable', 'string', 'max:100'],
            'offset'    => ['nullable', 'integer', 'min:0'],
            'limit'     => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'  => ['nullable', Rule::in(['id', 'expiry_date', 'received_at', 'quantity_remaining'])],
            'order_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
