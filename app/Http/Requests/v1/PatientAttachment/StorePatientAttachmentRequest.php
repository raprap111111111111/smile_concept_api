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
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'file_name'      => ['required', 'string', 'max:255'],
            'file_type'      => ['required', 'string', 'in:jpg,jpeg,png,pdf,dcm'],
            'category'       => ['required', 'string', 'in:xray,photo,consent_form,treatment_plan,lab_report,prescription,referral,other'],
            'is_xray'        => ['sometimes', 'boolean'],
            'notes'          => ['nullable', 'string', 'max:1000'],

            // ✅ Real file upload rule
            'file'           => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,dcm'],
        ];
    }

    // ✅ Convert is_xray = "1" (from FormData) to boolean
    protected function prepareForValidation(): void
    {
        if ($this->has('is_xray')) {
            $this->merge([
                'is_xray' => filter_var($this->is_xray, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}