<?php

namespace App\Domain\LabCases\DTOs;

final readonly class UpdateLabCaseDTO
{
    public function __construct(
        public ?int $appointmentId = null,
        public ?string $labName = null,
        public ?string $workType = null,
        public ?string $status = null,
        public ?string $sentDate = null,
        public ?string $dueDate = null,
        public ?string $receivedDate = null,
        public ?float $cost = null,
        public ?string $notes = null
    ) {}
}
