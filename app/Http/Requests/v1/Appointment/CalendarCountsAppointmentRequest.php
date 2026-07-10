<?php

namespace App\Http\Requests\v1\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarCountsAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        return $user->can('appointment.viewAny') 
            || $user->can('appointment.view');
    }

    /**
     * ✅ Check if user can view ALL appointments
     */
    public function canViewAny(): bool
    {
        return $this->user()->can('appointment.viewAny');
    }

    public function rules(): array
    {
        return [
            'month'     => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'status'    => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled', 'completed'])],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'user_id'   => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}