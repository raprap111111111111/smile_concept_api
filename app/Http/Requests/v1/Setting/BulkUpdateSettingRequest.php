<?php

namespace App\Http\Requests\v1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'settings'   => ['required', 'array', 'min:1'],
            'settings.*' => ['required'],
        ];
    }
}