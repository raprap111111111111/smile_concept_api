<?php

namespace App\Domain\DentalCharts\DTOs;

final readonly class CreateDentalChartDTO
{
    /**
     * @param CreateDentalChartEntryDTO[] $entries
     */
    public function __construct(
        public int $userId,
        public ?int $appointmentId,
        public ?string $generalNotes,
        public array $entries
    ) {}
}
