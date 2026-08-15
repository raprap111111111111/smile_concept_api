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
            // ── Core ─────────────────────────────────────────────────────
            'consent_template_id' => ['required', 'integer', 'exists:consent_templates,id'],
            'user_id'             => ['required', 'integer', 'exists:users,id'],
            'appointment_id'      => ['nullable', 'integer', 'exists:appointments,id'],
            'signature_data'      => ['required', 'string'],
            'sign_on_behalf_of'   => ['nullable', 'string', 'in:self,guardian,staff'],

            // ── form_data root ────────────────────────────────────────────
            'form_data'           => ['nullable', 'array'],

            // ── Patient Info ──────────────────────────────────────────────
            'form_data.patient_info'                     => ['nullable', 'array'],
            'form_data.patient_info.name'                => ['nullable', 'string', 'max:255'],
            'form_data.patient_info.birthdate'           => ['nullable', 'string', 'max:50'],
            'form_data.patient_info.religion'            => ['nullable', 'string', 'max:100'],
            'form_data.patient_info.home_address'        => ['nullable', 'string', 'max:500'],
            'form_data.patient_info.occupation'          => ['nullable', 'string', 'max:255'],
            'form_data.patient_info.dental_insurance'    => ['nullable', 'string', 'max:255'],
            'form_data.patient_info.effective_date'      => ['nullable', 'string', 'max:50'],
            'form_data.patient_info.guardian_name'       => ['nullable', 'string', 'max:255'],
            'form_data.patient_info.guardian_occupation' => ['nullable', 'string', 'max:255'],

            // ── Guardian Profile ──────────────────────────────────────────
            'form_data.guardian_profile'                        => ['nullable', 'array'],
            'form_data.guardian_profile.guardian_name'          => ['nullable', 'string', 'max:255'],
            'form_data.guardian_profile.guardian_relationship'  => ['nullable', 'string', 'max:100'],
            'form_data.guardian_profile.guardian_occupation'    => ['nullable', 'string', 'max:255'],
            'form_data.guardian_profile.guardian_address'       => ['nullable', 'string', 'max:500'],

            // ── Clauses ───────────────────────────────────────────────────
            'form_data.clauses'           => ['nullable', 'array'],
            'form_data.clauses.*.agreed'  => ['nullable', 'boolean'],
            'form_data.clauses.*.initial' => ['nullable', 'string', 'max:5'],

            // ── Medical ───────────────────────────────────────────────────
            'form_data.medical'                         => ['nullable', 'array'],
            'form_data.medical.in_good_health'          => ['nullable', 'boolean'],
            'form_data.medical.under_medical_treatment' => ['nullable', 'boolean'],
            'form_data.medical.had_serious_illness'     => ['nullable', 'boolean'],
            'form_data.medical.was_hospitalized'        => ['nullable', 'boolean'],
            'form_data.medical.takes_medications'       => ['nullable', 'boolean'],
            'form_data.medical.uses_tobacco'            => ['nullable', 'boolean'],
            'form_data.medical.uses_alcohol_drugs'      => ['nullable', 'boolean'],
            'form_data.medical.has_allergies'           => ['nullable', 'boolean'],
            'form_data.medical.is_pregnant'             => ['nullable', 'boolean'],
            'form_data.medical.treatment_condition'     => ['nullable', 'string', 'max:500'],
            'form_data.medical.illness_details'         => ['nullable', 'string', 'max:500'],
            'form_data.medical.hospitalization_details' => ['nullable', 'string', 'max:500'],
            'form_data.medical.medications'             => ['nullable', 'string', 'max:500'],
            'form_data.medical.bleeding_time'           => ['nullable', 'string', 'max:50'],
            'form_data.medical.blood_type'              => ['nullable', 'string', 'max:10'],
            'form_data.medical.blood_pressure'          => ['nullable', 'string', 'max:20'],
            'form_data.medical.other_allergies'         => ['nullable', 'string', 'max:500'],
            'form_data.medical.other_conditions'        => ['nullable', 'string', 'max:500'],
            'form_data.medical.conditions'              => ['nullable', 'array'],
            'form_data.medical.conditions.*'            => ['string'],
            'form_data.medical.allergy_types'           => ['nullable', 'array'],
            'form_data.medical.allergy_types.*'         => ['string'],

            // ── Intraoral ─────────────────────────────────────────────────
            'form_data.intraoral'                 => ['nullable', 'array'],
            'form_data.intraoral.selections'      => ['nullable', 'array'],
            'form_data.intraoral.legend_key_used' => ['nullable', 'string', 'max:50'],
        ];
    }
}