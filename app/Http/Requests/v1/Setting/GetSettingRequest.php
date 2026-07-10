<?php

namespace App\Http\Requests\v1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class GetSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.view');
    }

    public function rules(): array
    {
        return [];
    }
}