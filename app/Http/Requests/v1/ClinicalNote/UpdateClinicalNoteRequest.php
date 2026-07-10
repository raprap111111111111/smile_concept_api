<?php

namespace App\Http\Requests\v1\ClinicalNote;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $note = $this->route('clinical_note');
        return $note && $this->user()->can('update', $note);
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['sometimes', 'required', 'integer', 'exists:appointments,id'],
            'doctor_id' => ['sometimes', 'required', 'integer', 'exists:doctors,id'],
            'treatment_notes' => ['sometimes', 'required', 'string', 'max:5000'],
            'post_op_instructions' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'is_locked' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
