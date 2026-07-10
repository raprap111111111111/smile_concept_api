<?php

namespace App\Http\Requests\v1\DoctorSchedule;

use Illuminate\Foundation\Http\FormRequest;

class GetDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('doctor_schedule');
        return $schedule && $this->user()->can('view', $schedule);
    }

    public function rules(): array
    {
        return [];
    }
}
