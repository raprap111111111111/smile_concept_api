<?php

namespace App\Http\Requests\v1\PatientAttachment;

use Illuminate\Foundation\Http\FormRequest;

class DeletePatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attachment = $this->route('patient_attachment');
        return $attachment && $this->user()->can('delete', $attachment);
    }

    public function rules(): array
    {
        return [];
    }
}
