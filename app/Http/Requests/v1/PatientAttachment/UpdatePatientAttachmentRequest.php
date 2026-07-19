<?php
// UpdatePatientAttachmentRequest.php
namespace App\Http\Requests\v1\PatientAttachment;

use App\Models\PatientAttachment;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('patientAttachment'));
    }

    public function rules(): array
    {
        return [
            'user_id'        => ['sometimes', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'file_name'      => ['sometimes', 'string', 'max:255'],
            'file_path'      => ['sometimes', 'string', 'max:1000'],
            'file_type'      => ['sometimes', 'string', 'in:jpg,jpeg,png,pdf,dcm'],
            'category'       => ['sometimes', 'string', 'in:xray,photo,consent_form,treatment_plan,lab_report,prescription,referral,other'],
            'is_xray'        => ['sometimes', 'boolean'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}