<?php

namespace App\Http\Requests\v1\AppointmentReminder;

use Illuminate\Foundation\Http\FormRequest;

class GetAppointmentReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $reminder = $this->route('appointment_reminder');
        return $reminder && $this->user()->can('view', $reminder);
    }

    public function rules(): array
    {
        return [];
    }
}