<?php

namespace App\Http\Requests\v1\Prescription;

use App\Models\Prescription;
use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Prescription::class);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'], // Patient User ID
            'notes' => ['nullable', 'string', 'max:2000'],
            
            // Nested items Validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['required', 'string', 'max:100'],
            'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.duration_days' => ['required', 'integer', 'min:1'],
            'items.*.instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
