<?php

namespace App\Domain\DoctorSchedules\DTOs;

use App\Enums\DayOfWeek;

final readonly class UpdateDoctorScheduleDTO
{
    public function __construct(
        public ?int $doctorId = null,
        public ?int $branchId = null,
        public ?DayOfWeek $dayOfWeek = null,
        public ?string $startTime = null,
        public ?string $endTime = null,
    ) {}
}
