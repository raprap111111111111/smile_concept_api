<?php

namespace App\Http\Requests\Galleries;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gallery.create');
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'category' => 'nullable|string|max:100',
            'alt_text' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }
}
