<?php

namespace App\Http\Requests\v1\DoctorSchedule;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schedule = $this->route('doctor_schedule');
        return $schedule && $this->user()->can('delete', $schedule);
    }

    public function rules(): array
    {
        return [];
    }
}
