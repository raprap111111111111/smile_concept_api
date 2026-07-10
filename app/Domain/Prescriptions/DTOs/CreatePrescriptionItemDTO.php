<?php

namespace App\Domain\Prescriptions\DTOs;

final readonly class CreatePrescriptionItemDTO
{
    public function __construct(
        public string $medicineName,
        public string $dosage,
        public string $frequency,
        public int $durationDays,
        public ?string $instructions = null
    ) {}
}
