<?php

namespace App\Http\Requests\v1\AppointmentTreatment;

use Illuminate\Foundation\Http\FormRequest;

class GetAppointmentTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('appointment_treatment');
        return $item && $this->user()->can('view', $item);
    }

    public function rules(): array
    {
        return [];
    }
}