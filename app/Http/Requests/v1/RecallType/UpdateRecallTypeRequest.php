<?php

namespace App\Http\Requests\v1\RecallType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecallTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = $this->route('recall_type');
        return $type && $this->user()->can('update', $type);
    }

    public function rules(): array
    {
        $typeId = $this->route('recall_type')?->id;

        return [
            'label' => ['sometimes', 'required', 'string', 'max:100', "unique:recall_types,label,{$typeId}"],
            'slug' => ['sometimes', 'required', 'string', 'max:100', "unique:recall_types,slug,{$typeId}"],
            'frequency_months' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
