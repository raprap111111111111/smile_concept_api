<?php

namespace App\Http\Requests\v1\PatientProfile;

use App\Enums\BloodType;
use App\Models\PatientProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can create new patients
        return $this->user()->can('create', PatientProfile::class);
    }

    public function rules(): array
    {
        return [
            // ─── User account fields (required) ────────────
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],

            // ─── Medical profile fields (optional) ─────────
            'allergies'                            => ['nullable', 'string', 'max:1000'],
            'medical_history'                      => ['nullable', 'string', 'max:2000'],
            'blood_type'                           => ['nullable', 'string', Rule::enum(BloodType::class)],
            'emergency_contact_name'               => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone'              => ['nullable', 'string', 'max:20'],
            'requires_epinephrine_free_anesthesia' => ['nullable', 'boolean'],
            'has_cardiac_conditions'               => ['nullable', 'boolean'],
            'is_pregnant'                          => ['nullable', 'boolean'],
            'has_bleeding_disorders'               => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Patient name is required.',
            'email.required' => 'Patient email is required.',
            'email.unique'   => 'A user with this email already exists.',
        ];
    }
}