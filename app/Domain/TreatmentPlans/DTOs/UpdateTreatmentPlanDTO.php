<?php

namespace App\Domain\TreatmentPlans\DTOs;

use App\Enums\TreatmentPlanStatus;

final readonly class UpdateTreatmentPlanDTO
{
    /**
     * @param CreateTreatmentPlanItemDTO[]|null $items
     */
    public function __construct(
        public ?int $userId = null,
        public ?int $doctorId = null,
        public ?string $name = null,
        // TreatmentPlanMapper::fromUpdateRequest passes this and
        // UpdateTreatmentPlanAction reads it; without the property every
        // PUT /treatment-plans/{id} died on "Unknown named parameter $status".
        public ?TreatmentPlanStatus $status = null,
        public ?string $notes = null,
        public ?array $items = null
    ) {}
}
