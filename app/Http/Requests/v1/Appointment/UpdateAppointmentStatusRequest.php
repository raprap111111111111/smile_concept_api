<?php
// app/Http/Requests/v1/Appointment/UpdateAppointmentStatusRequest.php

namespace App\Http\Requests\v1\Appointment;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');
        $user = $this->user();

        return $user->can('update-status', $appointment) ||
               $user->isAdmin() ||
               $user->isAssistant();
    }

    public function rules(): array
    {
        return [
            'status'              => ['required', Rule::enum(AppointmentStatus::class)],
            'cancellation_reason' => [  // ✅ ADD
                'nullable',
                'string',
                'max:500',
                'required_if:status,cancelled',  // Required when cancelling
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                       => 'Status field is required.',
            'cancellation_reason.required_if'       => 'Cancellation reason is required when cancelling.',
        ];
    }
}