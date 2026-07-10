<?php

namespace App\Http\Requests\v1\ToothCondition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateToothConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $condition = $this->route('tooth_condition');
        return $condition && $this->user()->can('update', $condition);
    }

    public function rules(): array
    {
        $conditionId = $this->route('tooth_condition')?->id;

        return [
            'label' => ['sometimes', 'required', 'string', 'max:100', "unique:tooth_conditions,label,{$conditionId}"],
            'slug' => ['sometimes', 'required', 'string', 'max:100', "unique:tooth_conditions,slug,{$conditionId}"],
            'color_code' => ['sometimes', 'required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
