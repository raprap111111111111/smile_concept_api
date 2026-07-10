<?php

namespace App\Domain\DentalCharts\DTOs;

final readonly class CreateDentalChartEntryDTO
{
    public function __construct(
        public string $toothNumber,
        public int $condition, // Holds the tooth_condition_id
        public ?string $treatmentApplied = null
    ) {}
}
