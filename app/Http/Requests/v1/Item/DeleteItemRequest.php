<?php

namespace App\Http\Requests\v1\Item;

use Illuminate\Foundation\Http\FormRequest;

class DeleteItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');
        return $item && $this->user()->can('delete', $item);
    }

    public function rules(): array
    {
        return [];
    }
}
