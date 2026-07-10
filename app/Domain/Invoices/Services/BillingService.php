<?php

namespace App\Domain\Invoices\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

class BillingService
{
    private const EPSILON  = 0.005;
    private const DECIMALS = 2;

    /**
     * Compute subtotal for a single line item after discount.
     * Discount is treated as a fixed currency amount.
     */
    public function calculateItemTotal(
        float $unitPrice,
        int   $quantity,
        float $discount = 0.00
    ): float {
        $grossTotal = $unitPrice * $quantity;
        $netTotal   = $grossTotal - abs($discount);

        return max(0.00, round($netTotal, self::DECIMALS));
    }

    /**
     * Derive invoice status from total and remaining balance.
     * Uses epsilon comparison to mitigate floating-point drift.
     */
    public function determineStatus(float $totalAmount, float $balanceDue): InvoiceStatus
    {
        if ($this->lessThanOrEqual($totalAmount, 0.00)) {
            return InvoiceStatus::PAID;
        }

        if ($this->lessThanOrEqual($balanceDue, 0.00)) {
            return InvoiceStatus::PAID;
        }

        if ($this->greaterThanOrEqual($balanceDue, $totalAmount)) {
            return InvoiceStatus::UNPAID;
        }

        return InvoiceStatus::PARTIAL;
    }

    /**
     * Generate a race-safe sequential invoice number.
     * Format: INV-{YEAR}-{SEQUENCE} e.g. INV-2025-0001
     */
    public function generateInvoiceNumber(): string
    {
        $year  = now()->year;
        $start = now()->startOfYear()->toDateTimeString();
        $end   = now()->endOfYear()->toDateTimeString();

        $latest = Invoice::where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('invoice_number');

        // ✅ Replaces substr() — avoids Intelephense P1005 false positive
        $sequence = $latest
            ? ((int) explode('-', (string) $latest)[2]) + 1
            : 1;

        return sprintf('INV-%d-%04d', $year, $sequence);
    }
    /* ------------------------------------------------------------------
     | Float-safe comparison helpers
     | ------------------------------------------------------------------ */

    private function lessThanOrEqual(float $a, float $b): bool
    {
        return ($a - $b) <= self::EPSILON;
    }

    private function greaterThanOrEqual(float $a, float $b): bool
    {
        return ($a - $b) >= -self::EPSILON;
    }
}
