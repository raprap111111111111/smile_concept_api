<?php

namespace App\Domain\Treatments\DTOs;

final readonly class UpdateTreatmentDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?float $price = null,
        public ?int $estimatedDurationMinutes = null,
        public ?bool $isActive = null
    ) {}
}
