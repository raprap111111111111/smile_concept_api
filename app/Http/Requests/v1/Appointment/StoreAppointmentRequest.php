<?php

namespace App\Http\Requests\v1\Appointment;

use App\Enums\AppointmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Permission logic
        |--------------------------------------------------------------------------
        |
        | user_id missing:
        | - appointment is for authenticated user
        | - needs appointment.create
        |
        | user_id equals auth user:
        | - appointment is for self
        | - needs appointment.create
        |
        | user_id differs from auth user:
        | - appointment is for another patient
        | - needs appointment.create-for-others
        |
        */

        $targetUserId = $this->filled('user_id')
            ? (int) $this->input('user_id')
            : (int) $user->id;

        if ($targetUserId === (int) $user->id) {
            return $user->can('appointment.create');
        }

        return $user->can('appointment.create-for-others');
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if (!$user) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Do not force user_id here
        |--------------------------------------------------------------------------
        |
        | If user_id is missing, CreateAppointmentAction will fallback to auth user.
        | If user_id is present and user has no permission, authorize() will block.
        |
        */

        $this->merge([
            'created_by' => $user->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'doctor_id' => [
                'required',
                'integer',
                'exists:doctors,id',
            ],

            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
            ],

            'start_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:now',
            ],

            'end_time' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:start_time',
            ],

            'status' => [
                'nullable',
                Rule::enum(AppointmentStatus::class),
            ],

            'reason_for_visit' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |------------------------------------------------------------------
            | Patient contact details
            |------------------------------------------------------------------
            |
            | Captured when booking on someone else's behalf (spouse, child).
            | The appointment still belongs to user_id; these describe who is
            | actually attending.
            |
            */
            'patient_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'patient_phone' => [
                'nullable',
                'string',
                'max:255',
            ],

            'patient_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'additional_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'created_by' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'reminder_sent' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists'      => 'Selected patient does not exist.',
            'doctor_id.required'  => 'Doctor is required.',
            'branch_id.required'  => 'Branch is required.',
            'start_time.required' => 'Start time is required.',
            'start_time.after'    => 'Start time must be in the future.',
            'end_time.required'   => 'End time is required.',
            'end_time.after'      => 'End time must be after start time.',
        ];
    }
}