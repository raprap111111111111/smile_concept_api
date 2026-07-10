<?php

namespace App\Domain\DentalCharts\Services;

class DentalChartService
{
    /**
     * Validates dental numbering systems (e.g. Universal 1-32 or FDI 2-Digit)
     */
    public function validateToothNumber(string $toothNumber): void
    {
        $tooth = trim($toothNumber);

        if (is_numeric($tooth)) {
            $num = (int)$tooth;
            if ($num < 1 || $num > 32) {
                if ($num < 11 || $num > 85) {
                    throw new \InvalidArgumentException("Invalid tooth identifier '{$tooth}'.");
                }
            }
        } else {
            if (!preg_match('/^[A-Ta-t]$/', $tooth)) {
                throw new \InvalidArgumentException("Primary tooth identifier must be letters A through T.");
            }
        }
    }
}
