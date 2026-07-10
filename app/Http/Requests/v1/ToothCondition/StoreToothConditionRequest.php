<?php

namespace App\Http\Requests\v1\ToothCondition;

use App\Models\ToothCondition;
use Illuminate\Foundation\Http\FormRequest;

class StoreToothConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ToothCondition::class);
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100', 'unique:tooth_conditions,label'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:tooth_conditions,slug'],
            'color_code' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
