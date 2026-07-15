<?php

namespace App\Domain\TreatmentPlans\DTOs;

use App\Enums\TreatmentPlanStatus;

final readonly class ChangeTreatmentPlanStatusDTO
{
    public function __construct(
        public TreatmentPlanStatus $status,
        public ?string $reason = null,
        public ?int $changedBy = null,
    ) {}
}