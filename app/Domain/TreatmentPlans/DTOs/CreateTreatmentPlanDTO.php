<?php

namespace App\Domain\TreatmentPlans\DTOs;

final readonly class CreateTreatmentPlanDTO
{
    /**
     * @param CreateTreatmentPlanItemDTO[] $items
     */
    public function __construct(
        public int $userId,
        public int $doctorId,
        public string $name,
        public ?string $notes,
        public array $items
    ) {}
}
