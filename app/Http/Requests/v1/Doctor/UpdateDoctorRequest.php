<?php
// app/Http/Requests/v1/Doctor/UpdateDoctorRequest.php

namespace App\Http\Requests\v1\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = $this->route('doctor');
        return $doctor && $this->user()->can('update', $doctor);
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor')->id;
        
        return [
            'license_number'      => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'unique:doctors,license_number,' . $doctorId,
            ],
            'specialization'      => ['nullable', 'string', 'max:255'],
            'bio'                 => ['nullable', 'string', 'max:1000'],
            'consultation_fee'    => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:100'],
            'signature_path'      => ['nullable', 'string', 'max:255'],
            'is_active'           => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'license_number.unique' => 'This license number is already in use.',
        ];
    }
}