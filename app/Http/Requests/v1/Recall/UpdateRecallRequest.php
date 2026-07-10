<?php

namespace App\Http\Requests\v1\Recall;

use App\Enums\RecallStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecallRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recall = $this->route('recall');
        return $recall && $this->user()->can('update', $recall);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'recall_type_id' => ['sometimes', 'required', 'integer', 'exists:recall_types,id'],
            'due_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::enum(RecallStatus::class)],
            'last_notified_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
