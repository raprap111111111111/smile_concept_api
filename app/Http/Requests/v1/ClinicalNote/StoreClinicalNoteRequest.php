<?php

namespace App\Http\Requests\v1\ClinicalNote;

use App\Models\ClinicalNote;
use Illuminate\Foundation\Http\FormRequest;

class StoreClinicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ClinicalNote::class);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['required', 'integer', 'exists:appointments,id', 'unique:clinical_notes,appointment_id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'treatment_notes' => ['required', 'string', 'max:5000'],
            'post_op_instructions' => ['nullable', 'string', 'max:2000'],
            'is_locked' => ['nullable', 'boolean'],
        ];
    }
}
