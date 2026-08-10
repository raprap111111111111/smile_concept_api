<?php

namespace App\Http\Requests\v1\Consent;

use Illuminate\Foundation\Http\FormRequest;

class SignConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('consent-form.sign')
            || $this->user()->can('consentForm.sign');
    }

    public function rules(): array
    {
        return [
            'consent_template_id' => ['required', 'integer', 'exists:consent_templates,id'],
            'user_id'             => ['required', 'integer', 'exists:users,id'],
            'appointment_id'      => ['nullable', 'integer', 'exists:appointments,id'],
            'signature_data'      => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'A patient must be selected before signing.',
            'user_id.exists'   => 'The selected patient does not exist.',
        ];
    }
}