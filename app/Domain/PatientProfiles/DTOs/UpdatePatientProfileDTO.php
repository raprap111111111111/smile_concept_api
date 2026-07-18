<?php

namespace App\Domain\PatientProfiles\DTOs;

final readonly class UpdatePatientProfileDTO
{
    public function __construct(
        public ?int    $userId = null,
        public ?string $allergies = null,
        public ?string $medicalHistory = null,
        public ?string $bloodType = null,
        public ?string $emergencyContactName = null,
        public ?string $emergencyContactPhone = null,
        public ?bool   $requiresEpinephrineFreeAnesthesia = null,
        public ?bool   $hasCardiacConditions = null,
        public ?bool   $isPregnant = null,
        public ?bool   $hasBleedingDisorders = null,

        /**
         * Request keys the client actually sent, so a nullable field can be
         * cleared. Without this, null is ambiguous between "omitted" and
         * "set me back to empty".
         *
         * @var list<string>
         */
        public array $providedKeys = [],
    ) {}
}