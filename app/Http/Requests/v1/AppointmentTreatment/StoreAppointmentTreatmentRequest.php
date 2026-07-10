<?php

namespace App\Http\Requests\v1\AppointmentTreatment;

use App\Models\AppointmentTreatment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentTreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AppointmentTreatment::class);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id'],
            'treatment_id'   => ['required', 'integer', 'exists:treatments,id'],
            'tooth_number'   => ['nullable', 'string', 'max:5'],
            'price_charged'  => ['nullable', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}