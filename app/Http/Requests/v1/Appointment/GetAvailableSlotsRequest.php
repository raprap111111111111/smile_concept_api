<?php

namespace App\Http\Requests\v1\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailableSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('appointment.viewAny');
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'date'      => ['required', 'date_format:Y-m-d'],
        ];
    }
}