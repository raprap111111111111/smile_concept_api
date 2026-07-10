<?php

namespace App\Domain\Appointments\DTOs;

final readonly class CalendarCountsAppointmentDTO
{
    public function __construct(
        public string $month,
        public ?string $status = null,
        public ?int $doctorId = null,
        public ?int $branchId = null,
        public ?int $userId = null,
    ) {}
}