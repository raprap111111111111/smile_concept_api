<?php

namespace App\Domain\DentalChartEntries::DTOs; // Handled below with correct namespace

namespace App\Domain\DentalChartEntries\DTOs;

final readonly class CreateDentalChartEntryDTO
{
    public function __construct(
        public int $dentalChartId,
        public string $toothNumber,
        public int $toothConditionId,
        public ?string $treatmentApplied = null
    ) {}
}
