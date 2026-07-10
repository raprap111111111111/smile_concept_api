<?php

namespace App\Domain\PatientProfiles\DTOs;

final readonly class CreatePatientProfileDTO
{
    public function __construct(
        // User account fields (for creating new user)
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $password,

        // Medical profile fields
        public ?string $allergies,
        public ?string $medicalHistory,
        public ?string $bloodType,
        public ?string $emergencyContactName,
        public ?string $emergencyContactPhone,
        public bool $requiresEpinephrineFreeAnesthesia = false,
        public bool $hasCardiacConditions = false,
        public bool $isPregnant = false,
        public bool $hasBleedingDisorders = false,
    ) {}
}