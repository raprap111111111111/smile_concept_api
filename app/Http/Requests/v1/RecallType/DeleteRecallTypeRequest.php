<?php

namespace App\Http\Requests\v1\RecallType;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRecallTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $type = $this->route('recall_type');
        return $type && $this->user()->can('delete', $type);
    }

    public function rules(): array
    {
        return [];
    }
}
