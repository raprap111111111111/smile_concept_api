<?php

namespace App\Domain\TreatmentPlans\DTOs;



final readonly class UpdateTreatmentPlanDTO
{
    /**
     * @param CreateTreatmentPlanItemDTO[]|null $items
     */
    public function __construct(
        public ?int $userId = null,
        public ?int $doctorId = null,
        public ?string $name = null,
        public ?string $notes = null,
        public ?array $items = null
    ) {}
}
