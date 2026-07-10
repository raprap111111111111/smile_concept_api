<?php

namespace App\Http\Requests\v1\AppointmentTreatment;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAppointmentTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('appointment_treatment');
        return $item && $this->user()->can('delete', $item);
    }

    public function rules(): array
    {
        return [];
    }
}