<?php

namespace App\Http\Requests\v1\Appointment;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');
        return $appointment && $this->user()->can('delete', $appointment);
    }

    public function rules(): array
    {
        return [];
    }
}
