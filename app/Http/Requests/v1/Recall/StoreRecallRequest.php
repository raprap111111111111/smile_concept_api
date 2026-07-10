<?php

namespace App\Http\Requests\v1\Recall;

use App\Models\Recall;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Recall::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'recall_type_id' => ['required', 'integer', 'exists:recall_types,id'], // Validate dynamically
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
