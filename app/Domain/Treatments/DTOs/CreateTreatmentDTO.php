<?php

namespace App\Domain\Treatments\DTOs;

final readonly class CreateTreatmentDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public float $price,
        public int $estimatedDurationMinutes,
        public bool $isActive
    ) {}
}
