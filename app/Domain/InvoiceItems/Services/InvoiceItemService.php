<?php

namespace App\Domain\InvoiceItems\Services;

use App\Domain\Invoices\Services\BillingService;
use App\Models\Invoice;

class InvoiceItemService
{
    public function __construct(
        private readonly BillingService $billingService
    ) {}

    /**
     * Re-aggregates line items and recalibrates parent invoice totals and statuses
     */
    public function syncParentInvoiceTotals(Invoice $invoice): void
    {
        // 1. Calculate sum of all line items
        $newTotalAmount = (float) $invoice->items()->sum('total_price');

        // 2. Calculate sum of all payments processed
        $totalPaid = (float) $invoice->payments()->sum('amount');

        // 3. Outstanding balance difference
        $newBalance = max(0.00, round($newTotalAmount - $totalPaid, 2));

        // 4. Resolve the status using the billing helper
        $status = $this->billingService->determineStatus($newTotalAmount, $newBalance);

        $invoice->update([
            'total_amount' => $newTotalAmount,
            'balance_due' => $newBalance,
            'status' => $status,
        ]);
    }
}
