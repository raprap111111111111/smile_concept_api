<?php

namespace App\Http\Requests\v1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.update');
    }

    public function rules(): array
    {
        return [
            'value' => ['required'],  // Accept any type (string, bool, int, array)
        ];
    }
}