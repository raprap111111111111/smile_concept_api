<?php

namespace App\Http\Requests\v1\Inventory;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetStockMovementsRequest extends FormRequest
{
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        return $this->user()->can('inventory.viewAny');
    }

    public function rules(): array
    {
        return [
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'item_id'      => ['nullable', 'integer', 'exists:items,id'],
            'type'         => ['nullable', Rule::in(StockMovementType::values())],
            'performed_by' => ['nullable', 'integer', 'exists:users,id'],

            // BaseQueryApplier reads a date_range filter from these two keys.
            'created_at_from' => ['nullable', 'date'],
            'created_at_to'   => ['nullable', 'date', 'after_or_equal:created_at_from'],

            'search'    => ['nullable', 'string', 'max:100'],
            'offset'    => ['nullable', 'integer', 'min:0'],
            'limit'     => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'  => ['nullable', Rule::in(['id', 'created_at', 'quantity_delta'])],
            'order_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
