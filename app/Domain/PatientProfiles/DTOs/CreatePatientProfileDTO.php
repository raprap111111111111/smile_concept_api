<?php

namespace App\Domain\PatientProfiles\DTOs;

final readonly class CreatePatientProfileDTO
{
    public function __construct(
        // User account fields
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $password,

        // Demographics & Address
        public ?string $dateOfBirth = null,
        public ?string $gender = null,
        public ?string $civilStatus = null,
        public ?string $nationality = null,
        public ?string $occupation = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $postalCode = null,

        // Insurance & Referral
        public ?string $insuranceProvider = null,
        public ?string $insuranceNumber = null,
        public ?string $referredBy = null,

        // Medical profile fields
        public ?string $allergies = null,
        public ?string $medicalHistory = null,
        public ?string $bloodType = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public bool $requiresEpinephrineFreeAnesthesia = false,
        public bool $hasCardiacConditions = false,
        public bool $isPregnant = false,
        public bool $hasBleedingDisorders = false,
    ) {}
}