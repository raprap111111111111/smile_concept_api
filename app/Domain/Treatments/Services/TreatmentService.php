<?php

namespace App\Domain\Treatments\Services;

class TreatmentService
{
    /**
     * Business validation logic for catalog creation/updates
     */
    public function validatePricingAndDuration(float $price, int $duration): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException("Treatment baseline price cannot be negative.");
        }

        if ($duration < 5) {
            throw new \InvalidArgumentException("Estimated clinical duration must be at least 5 minutes.");
        }
    }
}
