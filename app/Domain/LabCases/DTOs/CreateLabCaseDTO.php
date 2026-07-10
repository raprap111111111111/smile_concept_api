<?php

namespace App\Domain\LabCases\DTOs;

final readonly class CreateLabCaseDTO
{
    public function __construct(
        public int $appointmentId,
        public string $labName,
        public string $workType,
        public string $status,
        public string $sentDate,
        public string $dueDate,
        public ?string $receivedDate = null,
        public ?float $cost = null,
        public ?string $notes = null
    ) {}
}
