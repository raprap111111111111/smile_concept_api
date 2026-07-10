<?php

namespace App\Http\Requests\v1\PatientAttachment;

use App\Models\PatientAttachment;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PatientAttachment::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'file_name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:1000'],
            'file_type' => ['required', 'string', 'in:xray,photo,document'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
