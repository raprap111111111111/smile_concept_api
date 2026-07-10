<?php

namespace App\Domain\Prescriptions\Services;

class PrescriptionService
{
    /**
     * Business validation logic for drug administration timelines
     */
    public function validateMedicationDuration(int $days): void
    {
        if ($days <= 0) {
            throw new \InvalidArgumentException("Medication duration days must be greater than zero.");
        }
    }
}
