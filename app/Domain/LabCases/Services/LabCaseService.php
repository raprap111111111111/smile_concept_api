<?php

namespace App\Domain\LabCases\Services;

use Carbon\Carbon;

class LabCaseService
{
    public function validateDates(string $sentDate, string $dueDate): void
    {
        if (Carbon::parse($dueDate)->lt(Carbon::parse($sentDate))) {
            throw new \InvalidArgumentException("Lab due date must be after the sent date.");
        }
    }
}
