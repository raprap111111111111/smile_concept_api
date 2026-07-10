<?php
// app/Domain/Doctor/DTOs/UpdateDoctorDTO.php

namespace App\Domain\Doctor\DTOs;

final readonly class UpdateDoctorDTO
{
    public function __construct(
        public ?string $licenseNumber = null,
        public ?string $specialization = null,
        public ?string $bio = null,
        public ?float $consultationFee = null,
        public ?int $yearsOfExperience = null,
        public ?string $signaturePath = null,
        public ?bool $isActive = null,
    ) {}

}