<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('service.create');
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'price' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price',
            'duration_minutes' => 'nullable|integer|min:1',
            'category' => 'nullable|string|max:100',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }
}
