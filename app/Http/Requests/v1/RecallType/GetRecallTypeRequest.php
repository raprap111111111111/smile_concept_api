<?php

namespace App\Http\Requests\v1\RecallType;

use Illuminate\Foundation\Http\FormRequest;

class GetRecallTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = $this->route('recall_type');
        return $type && $this->user()->can('view', $type);
    }

    public function rules(): array
    {
        return [];
    }
}
