<?php

namespace App\Http\Requests\v1\PatientAttachment;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attachment = $this->route('patient_attachment');
        return $attachment && $this->user()->can('view', $attachment);
    }

    public function rules(): array
    {
        return [];
    }
}
