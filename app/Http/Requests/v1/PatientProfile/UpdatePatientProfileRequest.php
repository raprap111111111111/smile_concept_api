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
        $profileId = $this->route('patient_profile')?->id;

        return [
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
                "unique:patient_profiles,user_id,{$profileId}",
            ],
            'allergies'               => ['sometimes', 'nullable', 'string', 'max:1000'],
            'medical_history'         => ['sometimes', 'nullable', 'string', 'max:2000'],
            'blood_type'              => ['sometimes', 'nullable', Rule::enum(BloodType::class)],
            'emergency_contact_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:20'],

            // ✅ Booleans should be nullable, not required
            'requires_epinephrine_free_anesthesia' => ['sometimes', 'boolean'],
            'has_cardiac_conditions'               => ['sometimes', 'boolean'],
            'is_pregnant'                          => ['sometimes', 'boolean'],
            'has_bleeding_disorders'               => ['sometimes', 'boolean'],
        ];
    }
}
