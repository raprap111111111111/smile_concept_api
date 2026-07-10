<?php

namespace App\Http\Requests\v1\Consent;

use App\Models\PatientConsent;
use Illuminate\Foundation\Http\FormRequest;

class SignConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PatientConsent::class);
    }

    public function rules(): array
    {
        return [
            'consent_template_id' => ['required', 'integer', 'exists:consent_templates,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'signature_data' => ['required', 'string'],
        ];
    }
}
