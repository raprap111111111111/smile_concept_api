<?php

namespace App\Http\Requests\v1\AppointmentTreatment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('appointment_treatment');
        return $item && $this->user()->can('update', $item);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['sometimes', 'required', 'integer', 'exists:appointments,id'],
            'treatment_id'   => ['sometimes', 'required', 'integer', 'exists:treatments,id'],
            'tooth_number'   => ['sometimes', 'nullable', 'string', 'max:5'],
            'price_charged'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}