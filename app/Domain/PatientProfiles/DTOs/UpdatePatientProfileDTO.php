<?php

namespace App\Domain\PatientProfiles\DTOs;

final readonly class UpdatePatientProfileDTO
{
    public function __construct(
        public ?int $userId = null,

        // User account fields
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,

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

        // Medical fields
        public ?string $allergies = null,
        public ?string $medicalHistory = null,
        public ?string $bloodType = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public ?bool $requiresEpinephrineFreeAnesthesia = null,
        public ?bool $hasCardiacConditions = null,
        public ?bool $isPregnant = null,
        public ?bool $hasBleedingDisorders = null,

        public array $providedKeys = [],
    ) {}
}