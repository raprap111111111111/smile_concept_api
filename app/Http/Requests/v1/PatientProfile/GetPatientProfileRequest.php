<?php

namespace App\Http\Requests\v1\PatientProfile;

use Illuminate\Foundation\Http\FormRequest;

class GetPatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->route('patient_profile');
        return $profile && $this->user()->can('view', $profile);
    }

    public function rules(): array
    {
        return [];
    }
}
