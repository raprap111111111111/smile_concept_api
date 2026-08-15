<?php

namespace App\Domain\TreatmentPlans\DTOs;

final readonly class CreateTreatmentPlanItemDTO
{
    public function __construct(
        public int $treatmentId,
        public int $sequenceOrder,
        public int $quantity = 1,
        public ?string $notes = null
    ) {}
}
