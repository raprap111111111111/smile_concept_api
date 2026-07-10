<?php
// app/Http/Requests/v1/Doctor/StoreDoctorRequest.php

namespace App\Http\Requests\v1\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Doctor::class);
    }

    public function rules(): array
    {
        return [
            // ─── Required ─────────────────────────────
            'user_id'             => ['required', 'integer', 'exists:users,id', 'unique:doctors,user_id'],
            'license_number'      => ['required', 'string', 'max:100', 'unique:doctors,license_number'],
            
            // ─── Optional (New Fields) ────────────────
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
            'user_id.unique'        => 'This user is already registered as a doctor.',
            'license_number.unique' => 'This license number is already in use.',
        ];
    }
}