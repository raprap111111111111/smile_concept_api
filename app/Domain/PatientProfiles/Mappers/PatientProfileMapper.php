<?php

namespace App\Domain\PatientProfiles\Mappers;

use App\Domain\PatientProfiles\DTOs\CreatePatientProfileDTO;
use App\Domain\PatientProfiles\DTOs\UpdatePatientProfileDTO;
use App\Http\Requests\v1\PatientProfile\StorePatientProfileRequest;
use App\Http\Requests\v1\PatientProfile\UpdatePatientProfileRequest;

class PatientProfileMapper
{
    public static function fromCreateRequest(StorePatientProfileRequest $request): CreatePatientProfileDTO
    {
        return new CreatePatientProfileDTO(
            // User fields
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            password: $request->validated('password'),

            // Demographics & Address
            dateOfBirth: $request->validated('date_of_birth'),
            gender: $request->validated('gender'),
            civilStatus: $request->validated('civil_status'),
            nationality: $request->validated('nationality'),
            occupation: $request->validated('occupation'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            province: $request->validated('province'),
            postalCode: $request->validated('postal_code'),

            // Insurance & Referral
            insuranceProvider: $request->validated('insurance_provider'),
            insuranceNumber: $request->validated('insurance_number'),
            referredBy: $request->validated('referred_by'),

            // Medical fields
            allergies: $request->validated('allergies'),
            medicalHistory: $request->validated('medical_history'),
            bloodType: $request->validated('blood_type'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone'),
            requiresEpinephrineFreeAnesthesia: (bool) $request->validated('requires_epinephrine_free_anesthesia', false),
            hasCardiacConditions: (bool) $request->validated('has_cardiac_conditions', false),
            isPregnant: (bool) $request->validated('is_pregnant', false),
            hasBleedingDisorders: (bool) $request->validated('has_bleeding_disorders', false),
        );
    }

    public static function fromUpdateRequest(UpdatePatientProfileRequest $request): UpdatePatientProfileDTO
    {
        return new UpdatePatientProfileDTO(
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),

            // Demographics & Address
            dateOfBirth: $request->validated('date_of_birth'),
            gender: $request->validated('gender'),
            civilStatus: $request->validated('civil_status'),
            nationality: $request->validated('nationality'),
            occupation: $request->validated('occupation'),
            address: $request->validated('address'),
            city: $request->validated('city'),
            province: $request->validated('province'),
            postalCode: $request->validated('postal_code'),

            // Insurance & Referral
            insuranceProvider: $request->validated('insurance_provider'),
            insuranceNumber: $request->validated('insurance_number'),
            referredBy: $request->validated('referred_by'),

            // Medical fields
            allergies: $request->validated('allergies'),
            medicalHistory: $request->validated('medical_history'),
            bloodType: $request->validated('blood_type'),
            emergencyContactName: $request->validated('emergency_contact_name'),
            emergencyContactPhone: $request->validated('emergency_contact_phone'),
            requiresEpinephrineFreeAnesthesia: $request->has('requires_epinephrine_free_anesthesia') ? (bool) $request->validated('requires_epinephrine_free_anesthesia') : null,
            hasCardiacConditions: $request->has('has_cardiac_conditions') ? (bool) $request->validated('has_cardiac_conditions') : null,
            isPregnant: $request->has('is_pregnant') ? (bool) $request->validated('is_pregnant') : null,
            hasBleedingDisorders: $request->has('has_bleeding_disorders') ? (bool) $request->validated('has_bleeding_disorders') : null,

            providedKeys: array_keys($request->validated()),
        );
    }
}