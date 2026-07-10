<?php

namespace App\Http\Requests\v1\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class DeleteInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inventory = $this->route('inventory');
        return $inventory && $this->user()->can('delete', $inventory);
    }

    public function rules(): array
    {
        return [];
    }
}
