<?php

namespace App\Http\Requests\v1\Setting;

use Illuminate\Foundation\Http\FormRequest;

class GetSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // GetSettingRequest.php  (already correct)
        return $this->user()->can('setting.view');
    }

    public function rules(): array
    {
        return [];
    }
}
