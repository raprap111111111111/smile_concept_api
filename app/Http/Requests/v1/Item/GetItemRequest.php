<?php

namespace App\Http\Requests\v1\Item;

use Illuminate\Foundation\Http\FormRequest;

class GetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');
        return $item && $this->user()->can('view', $item);
    }

    public function rules(): array
    {
        return [];
    }
}
