<?php

namespace App\Http\Requests\v1\PatientProfile;

use App\Enums\BloodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->route('patient_profile');
        return $profile && $this->user()->can('update', $profile);
    }

    public function rules(): array
    {
        $profile   = $this->route('patient_profile');
        $profileId = $profile?->id;

        return [
            // ─── User account fields ───────────────────────
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($profile?->user_id),
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            'user_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
                "unique:patient_profiles,user_id,{$profileId}",
            ],

            // ─── Demographics & Address ────────────────────
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'gender'        => ['sometimes', 'nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'civil_status'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'nationality'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'occupation'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'address'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'city'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'province'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'postal_code'   => ['sometimes', 'nullable', 'string', 'max:20'],

            // ─── Insurance & Referral ──────────────────────
            'insurance_provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'insurance_number'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'referred_by'        => ['sometimes', 'nullable', 'string', 'max:255'],

            // ─── Medical fields ────────────────────────────
            'allergies'               => ['sometimes', 'nullable', 'string', 'max:1000'],
            'medical_history'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'blood_type'              => ['sometimes', 'nullable', Rule::enum(BloodType::class)],
            'emergency_contact_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            'requires_epinephrine_free_anesthesia' => ['sometimes', 'boolean'],
            'has_cardiac_conditions'               => ['sometimes', 'boolean'],
            'is_pregnant'                          => ['sometimes', 'boolean'],
            'has_bleeding_disorders'               => ['sometimes', 'boolean'],
        ];
    }
}