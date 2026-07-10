<?php

namespace App\Domain\Items\Services;

use Illuminate\Support\Str;

class ItemService
{
    /**
     * Clean and format SKU (Stock Keeping Unit) strings to standard uppercase formats
     */
    public function formatSku(string $sku): string
    {
        // Strip out whitespace and make uppercase
        return Str::upper(preg_replace('/\s+/', '', $sku));
    }

    /**
     * Ensure stock alert thresholds are structurally valid
     */
    public function validateThreshold(int $threshold): void
    {
        if ($threshold < 0) {
            throw new \InvalidArgumentException("Minimum safety stock threshold cannot be negative.");
        }
    }
}
