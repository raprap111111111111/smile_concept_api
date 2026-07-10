<?php

namespace App\Http\Requests\v1\RecallType;

use App\Models\RecallType;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecallTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', RecallType::class);
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100', 'unique:recall_types,label'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:recall_types,slug'],
            'frequency_months' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
