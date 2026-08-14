<?php

namespace App\Http\Requests\v1\AppointmentSetting;

use Illuminate\Foundation\Http\FormRequest;

class GetAppointmentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.view');
    }

    public function rules(): array
    {
        return [];
    }
}
