<?php

namespace App\Http\Requests\v1\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class GetInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inventory = $this->route('inventory');
        return $inventory && $this->user()->can('view', $inventory);
    }

    public function rules(): array
    {
        return [];
    }
}
