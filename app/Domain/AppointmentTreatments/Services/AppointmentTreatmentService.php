<?php

namespace App\Domain\AppointmentTreatments\Services;

use App\Models\Treatment;

class AppointmentTreatmentService
{
    /**
     * Resolve the price to charge — either the override passed in
     * or fall back to the treatment's base price.
     */
    public function resolvePrice(int $treatmentId, ?float $overridePrice = null): float
    {
        if ($overridePrice !== null && $overridePrice >= 0) {
            return round($overridePrice, 2);
        }

        $treatment = Treatment::findOrFail($treatmentId);

        return round((float) $treatment->price, 2);
    }

    /**
     * Validate that the tooth number follows FDI notation (11-48) or is null.
     */
    public function validateToothNumber(?string $toothNumber): void
    {
        if ($toothNumber === null) {
            return;
        }

        // Accept FDI 2-digit notation (11-48) OR universal (1-32)
        if (!preg_match('/^([1-4][1-8]|[1-9]|[12][0-9]|3[0-2])$/', $toothNumber)) {
            throw new \InvalidArgumentException(
                "Invalid tooth number [{$toothNumber}]. Use FDI (11-48) or Universal (1-32) notation."
            );
        }
    }
}