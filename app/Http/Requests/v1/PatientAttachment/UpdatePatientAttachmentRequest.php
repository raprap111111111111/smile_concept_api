<?php

namespace App\Http\Requests\v1\PatientAttachment;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attachment = $this->route('patient_attachment');
        return $attachment && $this->user()->can('update', $attachment);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'appointment_id' => ['sometimes', 'nullable', 'integer', 'exists:appointments,id'],
            'file_name' => ['sometimes', 'required', 'string', 'max:255'],
            'file_path' => ['sometimes', 'required', 'string', 'max:1000'],
            'file_type' => ['sometimes', 'required', 'string', 'in:xray,photo,document'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
