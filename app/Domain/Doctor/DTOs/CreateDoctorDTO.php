<?php
// app/Domain/Doctor/DTOs/CreateDoctorDTO.php

namespace App\Domain\Doctor\DTOs;

final readonly class CreateDoctorDTO
{
    public function __construct(
        public int $userId,
        public string $licenseNumber,
        public ?string $specialization = null,
        public ?string $bio = null,
        public ?float $consultationFee = null,
        public int $yearsOfExperience = 0,
        public ?string $signaturePath = null,
        public bool $isActive = true,
    ) {}

}