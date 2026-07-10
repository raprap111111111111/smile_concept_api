<?php

namespace App\Domain\DentalChartEntries\DTOs;

final readonly class UpdateDentalChartEntryDTO
{
    public function __construct(
        public ?int $dentalChartId = null,
        public ?string $toothNumber = null,
        public ?int $toothConditionId = null,
        public ?string $treatmentApplied = null
    ) {}
}
