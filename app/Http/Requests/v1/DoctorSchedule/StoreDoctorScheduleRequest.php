<?php

namespace App\Http\Requests\v1\DoctorSchedule;

use App\Enums\DayOfWeek;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', DoctorSchedule::class);
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'day_of_week' => ['required', new Enum(DayOfWeek::class)], // Standard index week limits
            'start_time' => ['required', 'date_format:H:i:s'],
            'end_time' => ['required', 'date_format:H:i:s'],
        ];
    }
}
