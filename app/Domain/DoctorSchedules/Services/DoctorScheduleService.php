<?php

namespace App\Domain\DoctorSchedules\Services;

class DoctorScheduleService
{
    /**
     * Validates that the schedule time parameters are structurally logical
     */
    public function validateTimeInterval(string $startTime, string $endTime): void
    {
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        if ($start === false || $end === false) {
            throw new \InvalidArgumentException("Invalid time formatting provided.");
        }

        if ($start >= $end) {
            throw new \InvalidArgumentException("Start time must be chronologically earlier than end time.");
        }
    }
}
