<?php
// app/Http/Requests/v1/Appointment/UpdateAppointmentRequest.php

namespace App\Http\Requests\v1\Appointment;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');

        if (!$appointment) {
            return false;
        }

        // Full update (staff) OR reschedule (patient — own active appointment)
        return $this->user()->can('update', $appointment)
            || $this->user()->can('reschedule', $appointment);
    }

    public function rules(): array
    {
        // Reschedule-only users (patients) may change time + reason only.
        // Other submitted fields are stripped because validated() ignores them.
        if (!$this->user()->can('appointment.update')) {
            return [
                'start_time'       => ['required', 'date_format:Y-m-d H:i:s', 'after:now'],
                'end_time'         => ['required', 'date_format:Y-m-d H:i:s', 'after:start_time'],
                'reason_for_visit' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return [
            'user_id'             => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'doctor_id'           => ['sometimes', 'required', 'integer', 'exists:doctors,id'],
            'branch_id'           => ['sometimes', 'required', 'integer', 'exists:branches,id'],
            'start_time'          => ['sometimes', 'required', 'date_format:Y-m-d H:i:s', 'after:now'],
            'end_time'            => ['sometimes', 'required', 'date_format:Y-m-d H:i:s', 'after:start_time'],
            'status'              => ['nullable', Rule::enum(AppointmentStatus::class)],
            'reason_for_visit'    => ['nullable', 'string', 'max:1000'],       // ✅ ADD
            'cancellation_reason' => ['nullable', 'string', 'max:500'],        // ✅ ADD
            'reminder_sent'       => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists'   => 'User not found',
            'doctor_id.exists' => 'Doctor not found',
            'branch_id.exists' => 'Branch not found',
            'start_time.after' => 'Start time must be in the future',
            'end_time.after'   => 'End time must be after start time',
        ];
    }
}