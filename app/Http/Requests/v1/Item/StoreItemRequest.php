<?php

namespace App\Http\Requests\v1\Item;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Item::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku'],
            'category' => ['required', 'string', 'max:100'], // e.g., PPE, Restorative, Anesthetics
            'unit_of_measure' => ['required', 'string', 'max:50'], // e.g., box, bottle, piece
            'minimum_threshold' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
