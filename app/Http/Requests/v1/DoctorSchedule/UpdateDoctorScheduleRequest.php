<?php

namespace App\Http\Requests\v1;

namespace App\Http\Requests\v1\DoctorSchedule;

use App\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('doctor_schedule');
        return $schedule && $this->user()->can('update', $schedule);
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctors,id'],
            'branch_id' => ['sometimes', 'required', 'integer', 'exists:branches,id'],
            'day_of_week' => ['sometimes', new Enum(DayOfWeek::class)],
            'start_time' => ['sometimes', 'required', 'date_format:H:i:s'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i:s'],
        ];
    }
}
