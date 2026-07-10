<?php

namespace App\Http\Requests\v1\ActivityLog;

use Illuminate\Foundation\Http\FormRequest;

class GetAllActivityLogsRequest extends FormRequest
{
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        return $this->user()->can('activity_logs.viewAny');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit'     => max(1, min(self::MAX_LIMIT, (int) $this->input('limit', 20))),
            'order_by'  => in_array($this->input('order_by'), ['id', 'action', 'created_at']) ? $this->input('order_by') : 'created_at',
            'order_dir' => in_array(strtolower($this->input('order_dir')), ['asc', 'desc']) ? strtolower($this->input('order_dir')) : 'desc',
        ]);
    }

    public function rules(): array
    {
        return [
            'search'       => ['nullable', 'string', 'max:100'],
            'user_id'      => ['nullable', 'integer', 'exists:users,id'],
            'action'       => ['nullable', 'string', 'max:50'],
            'subject_type' => ['nullable', 'string', 'max:100'],
            'subject_id'   => ['nullable', 'integer'],
            'from_date'    => ['nullable', 'date'],
            'to_date'      => ['nullable', 'date', 'after_or_equal:from_date'],
            'offset'       => ['nullable', 'integer', 'min:0'],
            'limit'        => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'     => ['nullable', 'string'],
            'order_dir'    => ['nullable', 'string'],
        ];
    }
}