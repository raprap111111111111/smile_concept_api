<?php

namespace App\Http\Requests\v1\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailableSlotsRequest extends FormRequest
{
    /**
     * 🔐 AUTHORIZATION
     *
     * - appointment.viewAny → Admin/Staff booking on a patient's behalf
     * - appointment.create  → Patient booking for themselves
     *
     * A caller allowed to create an appointment has to see which times are
     * open, or the permission can't be exercised. The response carries only
     * slot start/end and a free/busy flag for one doctor on one day — no
     * appointment detail and no other patient's identity — so `create` alone
     * is enough. Mirrors CalendarCountsAppointmentRequest, which already lets
     * a booking patient read clinic-wide day totals.
     */
    public function authorize(): bool
    {
        return $this->user()->canAny([
            'appointment.viewAny',
            'appointment.create',
        ]);
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