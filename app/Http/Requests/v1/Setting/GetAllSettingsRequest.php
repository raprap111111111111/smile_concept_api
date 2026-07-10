<?php

namespace App\Http\Requests\v1\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAllSettingsRequest extends FormRequest
{
    private const MAX_LIMIT = 100;

    public function authorize(): bool
    {
        return $this->user()->can('settings.viewAny');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'limit'     => max(1, min(self::MAX_LIMIT, (int) $this->input('limit', 50))),
            'order_by'  => in_array($this->input('order_by'), ['id', 'key', 'group', 'created_at']) ? $this->input('order_by') : 'group',
            'order_dir' => in_array(strtolower($this->input('order_dir')), ['asc', 'desc']) ? strtolower($this->input('order_dir')) : 'asc',
        ]);
    }

    public function rules(): array
    {
        return [
            'search'    => ['nullable', 'string', 'max:100'],
            'group'     => ['nullable', 'string', 'max:50'],
            'type'      => ['nullable', Rule::in(['string', 'integer', 'float', 'boolean', 'json', 'date'])],
            'is_public' => ['nullable', 'boolean'],
            'offset'    => ['nullable', 'integer', 'min:0'],
            'limit'     => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'order_by'  => ['nullable', 'string'],
            'order_dir' => ['nullable', 'string'],
        ];
    }
}