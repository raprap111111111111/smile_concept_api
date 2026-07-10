<?php

namespace App\Domain\DentalCharts\DTOs;

final readonly class UpdateDentalChartDTO
{
    /**
     * @param CreateDentalChartEntryDTO[]|null $entries
     */
    public function __construct(
        public ?int $userId = null,
        public ?int $appointmentId = null,
        public ?string $generalNotes = null,
        public ?array $entries = null
    ) {}
}
