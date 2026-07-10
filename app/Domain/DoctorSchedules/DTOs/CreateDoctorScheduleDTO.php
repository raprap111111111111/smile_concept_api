<?php

namespace App\Domain\DoctorSchedules\DTOs;

use App\Enums\DayOfWeek;

final readonly class CreateDoctorScheduleDTO
{
    public function __construct(
        public int $doctorId,
        public int $branchId,
        public DayOfWeek $dayOfWeek,
        public string $startTime,
        public string $endTime,
    ) {}
}
